<header>
    <div class="left">
        <a href="./index.php">home</a>
    </div>

    <div class="right">
        <?php
        
        if(!isset($_SESSION['login'])){
            echo "<a href=\"./index.php?do=login\">login</a>";
            echo "<a href=\"./index.php?do=register\">register</a>";
        }else {
            echo "<a href=\"./index.php?do=logout\">logout</a>";
            echo "<a href=\"./index.php?do=console\">console</a>";
        }
        
        ?>
    </div>
</header>