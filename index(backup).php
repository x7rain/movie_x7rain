<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>
<body>
    <!--поиск-->
    <form action="" method="POST">
        <input type="text" name="mquery" >
        <button name="SubmitButtonS">Поиск</button>
        <br>
        Фильтры:
        <!--фильтр по году-->
        год
        <input type="number" name="fromDate" placeholder='от'>
        <input type="number" name="toDate" placeholder='до'>
        бюджет
        <!--фильтр по бюджету-->
        <input type="number" name="fromBudget" placeholder='от'>
        <input type="number" name="toBudget" placeholder='до'>
    </form>
    <!--форма новый фильм-->
    <form enctype="multipart/form-data" method="POST" action="">
        <label for="mtitle">Название</label>
        <input type="text" name="mtitle" required>
        <label for="mdate">Дата Выхода</label>
        <input type="date" name="mdate" required>
        <label for="mdirector">Режиссер</label>
        <input type="text" name="mdirector" required>
        <label for="mbudget">Бюджет</label>
        <input type="number" name="mbudget" min='0' max='10000000000'>
        <label for="mposter">Постер</label>
        <input type="file" name="mposter" required>  
        <input type="submit" name="SubmitButtonN">      
    </form>
    <!--отправка формы в бд-->
    <?php  
        include('connection.php');      
        if(isset($_POST['SubmitButtonN'])){               
            $dbh = con();
    
            $title=$_POST['mtitle'];
            $budget=$_POST['mbudget'];
            $date=$_POST['mdate'];
            $director=$_POST['mdirector'];
            $poster=basename($_FILES['mposter']['name']);

            $data = array('title' => $title, 'date' => $date, 'director' => $director,'budget' => $budget,  'poster' => $poster); 

            $query = $dbh->prepare("INSERT INTO movies (title, date, director, budget, poster) VALUES (:title, :date, :director, :budget, :poster)");
            $query->execute($data);
            //сохранение постера на сервер
            $uploaddir = 'C:\Users\Vlad\repository\movie_x7rain\uploads\posters\\';
            $uploadfile = $uploaddir . basename($_FILES['mposter']['name']);
            move_uploaded_file($_FILES['mposter']['tmp_name'], $uploadfile);
        }  
    ?>    
    <!-- обращение к бд -->
    <?php                  
        //стандартные значения поиска и фильтров
        $mquery = '';
        $fDate = '1887';
        $tDate = '2200';
        $fBudget = '0';
        $tBudget = '10000000000';
        //текущие значения поиска и фильтров
        if(isset($_POST['mquery'])){  
            global $mquery;
            $mquery = $_POST['mquery'];}
        if(isset($_POST['fromDate']) && $_POST['fromDate'] != ""){  
            global $fDate;
            $fDate = $_POST['fromDate'];}
        if(isset($_POST['toDate']) && $_POST['toDate'] != ""){  
            global $tDate;
            $tDate = $_POST['toDate'];}
        if(isset($_POST['fromBudget']) && $_POST['fromBudget'] != ""){  
            global $fBudget;
            $fBudget = $_POST['fromBudget'];}
        if(isset($_POST['toBudget']) && $_POST['toBudget'] != ""){  
            global $tBudget;
            $tBudget = $_POST['toBudget'];}   
        //определяем запрос
        $query = "
            SELECT title, date, director, budget, poster
            FROM movies
            WHERE title LIKE '%" . $mquery . "%'
            AND budget >= " . $fBudget . " AND budget <= " . $tBudget . "
            AND date >= " . $fDate . "0101 AND date <= " . $tDate . "1231
            ORDER BY date;
        ";
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // проверка ошибок
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); // создаем соеденение        
        } catch (mysqli_sql_exception $e) {
            error_log($e->__toString());
        }            
        $result = $conn->query($query); //отправляем запрос по соеденению  
        //вывод списком
        $uploaddir = "/repository/movie_x7rain/uploads/posters/"; //место куда сохранены постеры
        echo "<ul>";       
        while($row = $result->fetch_array(MYSQLI_ASSOC)){
            $budget = $row["budget"] . '$';
            if ($row["budget"] == ''){       
                $budget = 'Бюджет не указан';
            }            
            echo "<li>", $row["title"], " ", $row["date"], " ", $row["director"], " ", $budget, " ";
            echo "<img src='" . $uploaddir . $row["poster"] . "' alt='poster' height='200' width='142'> </li>";
        };
        echo "</ul>";           
    ?>
    <!-- пагинация -->   
      
</body>
</html>