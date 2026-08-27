<?php
// ============================================================================
// 1. 系統初始化與時區安全鎖定
// ============================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set("Asia/Taipei");

// 引入您的外部資料庫隔離設定檔
$config = require __DIR__ . "/../../db_config/accounting/db_config.php";

// ============================================================================
// 2. 核心大腦：通用型資料庫操作類別 (Database Query Builder Engine)
// ============================================================================
class DB
{
    protected $dsn;
    protected $pdo;
    protected $table;

    /**
     * 建構子：自動初始化 PDO 連線與指定資料表
     */
    public function __construct($table)
    {
        global $config;

        $this->dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";

        if ($config['driver'] == "mysql") {
            $this->dsn .= ";charset=utf8mb4";
        }

        $this->pdo = new PDO($this->dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $this->table = $table;
    }

    /**
     * 輔助函數：將關聯陣列轉為 SQL 的 "欄位 = :欄位" 名標籤綁定規格
     */
    protected function a2s($array)
    {
        $tmp = [];
        foreach ($array as $key => $value) {
            $tmp[] = "`$key` = :$key";
        }
        return $tmp;
    }

    /**
     * 💡 擴充機制：允許外部取得 PDO 實例（未來後端寫複式簿記 Transaction 事務時不可或缺！）
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * 讀取全部資料 (💡 自動在底層幫您鎖定只查詢沒被軟刪除的正常資料)
     */
    public function all($where = null, $next = null)
    {
        $sql = "SELECT * FROM `$this->table` WHERE `deleted_at` IS NULL";
        $bindings = [];

        if (!empty($where)) {
            if (is_array($where)) {
                $sql .= " AND " . join(" AND ", $this->a2s($where));
                $bindings = $where;
            } else {
                $sql .= " AND " . trim($where);
            }
        }

        if (!empty($next)) {
            $sql .= " " . trim($next);
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);
        return $statement->fetchAll();
    }

    /**
     * 加總筆數 (💡 自動加入沒被軟刪除的防護線)
     */
    public function count($where = null, $next = null)
    {
        $sql = "SELECT COUNT(*) FROM `$this->table` WHERE `deleted_at` IS NULL";
        $bindings = [];

        if (!empty($where)) {
            if (is_array($where)) {
                $sql .= " AND " . join(" AND ", $this->a2s($where));
                $bindings = $where;
            } else {
                $sql .= " AND " . trim($where);
            }
        }

        if (!empty($next)) {
            $sql .= " " . trim($next);
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);
        return $statement->fetchColumn();
    }

    /**
     * 查詢單一資料 (💡 已安全補上 $next = null 預設值，且支援純陣列智慧查詢，徹底防範型態崩潰)
     */
    public function find($where, $next = null)
    {
        $sql = "SELECT * FROM `$this->table` WHERE `deleted_at` IS NULL AND ";
        $bindings = [];

        if (is_array($where)) {
            $sql .= join(" AND ", $this->a2s($where));
            $bindings = $where;
        } else {
            $sql .= "`id` = :id";
            $bindings = ['id' => $where];
        }

        if (!empty($next)) {
            $sql .= " " . trim($next);
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);
        return $statement->fetch();
    }

    /**
     * 智慧型儲存功能：自動判定執行新增 (INSERT) 或修改 (UPDATE)
     */
    public function save($where)
    {
        if (isset($where['id'])) {
            $id = $where['id'];
            unset($where['id']);
            $sql = "UPDATE `$this->table` SET " . join(", ", $this->a2s($where)) . " WHERE `id` = :id";
            $where['id'] = $id;
        } else {
            $fields = array_keys($where);
            $placeholders = array_map(function ($field) {
                return ":$field";
            }, $fields);
            $sql = "INSERT INTO `$this->table` (`" . join("`, `", $fields) . "`) VALUES (" . join(", ", $placeholders) . ")";
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($where);
        
        return isset($id) ? $id : $this->pdo->lastInsertId();
    }

    /**
     * 刪除功能 (💡 完美升級：將物理刪除改裝為軟刪除機制，打上時間戳記，不破壞外鍵勾稽)
     */
    public function delete($where)
    {
        $sql = "UPDATE `$this->table` SET `deleted_at` = NOW() WHERE ";
        $bindings = [];

        if (is_array($where)) {
            $sql .= join(" AND ", $this->a2s($where));
            $bindings = $where;
        } else {
            $sql .= "`id` = :id";
            $bindings = ['id' => $where];
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);
    }

    /**
     * 智慧複雜查詢通道：用來通吃未來 api 與報表所需要的巨型多表 JOIN 或 RAND() 查詢
     */
    public function q($sql, $bindings = [])
    {
        $statement = $this->pdo->prepare($sql);
        if (!empty($bindings)) {
            $statement->execute($bindings);
        } else {
            $statement->execute();
        }
        return $statement->fetchAll();
    }
}

// ============================================================================
// 3. 全局通用輔助函數
// ============================================================================

/**
 * 網頁重新導向跳轉
 */
function to($url)
{
    header("Location: $url");
    exit();
}

/**
 * 專業排版級陣列除錯器
 */
function dd($array)
{
    echo "<pre style='background:#222; color:#00ff00; padding:15px; border-radius:6px; font-family:Courier New;'>";
    print_r($array);
    echo "</pre>";
}

// ============================================================================
// 4. 🖨️ 實例化全域物件區（完美對齊最新 10 欄位 users 結構與 12 核心表）
// ============================================================================
$User      = new DB('users');
$Ledger    = new DB('ledgers');
$Account   = new DB('accounts');
$Category  = new DB('categories');
$Partner   = new DB('partners');
$Period    = new DB('accounting_periods');
$Balance   = new DB('account_balances');
$Tx        = new DB('transactions');
$TxLine    = new DB('transaction_lines');
$Attach    = new DB('attachments');
$Log       = new DB('audit_logs');
$Perm      = new DB('user_ledger_permissions');
?>