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
    <div class='page'>
        <!--поиск и фильтры--> 
        <div class='sandf'>       
        <form action="" method="GET">
                <div class='search'>            
                    <input type="text" name="mquery" id='searchIn'>
                    <button id='searchBu'><img src="/repository/movie_x7rain/img/sr.png" alt="Поиск" height=20px></button>   
                </div>         
            <details>
                <summary class='summary1'>Фильтры</summary>
                <div class='det'>    
                    <!--фильтр по году-->
                    <div class = 'input-details'>
                        + Год:
                        <input type="number" name="fromDate" placeholder='от' min='0' max='9999'>
                        <input type="number" name="toDate" placeholder='до' min='0' max='9999'>
                    </div>
                    <div class = 'input-details'>
                        + Бюджет($):
                        <!--фильтр по бюджету-->
                        <input type="number" name="fromBudget" placeholder='от' min='0' max='9999999999'>
                        <input type="number" name="toBudget" placeholder='до' min='0' max='9999999999'>
                    </div>
                </div>
            </details> 
        </form>        
        <!--форма новый фильм-->
        <details>
            <summary class='summary2'>Добавить фильм</summary>
            <div class='det'>
                <form class='form2' enctype="multipart/form-data" method="POST" action="">
                    <div class = 'input-details2'>
                        <label for="mtitle">Название</label>
                        <input type="text" name="mtitle" required>
                    </div>
                    <div class = 'input-details2'>
                        <label for="mdate">Дата Выхода</label>
                        <input type="date" name="mdate" id="mdate" required>
                    </div>
                    <div class = 'input-details2'>
                        <label for="mdirector">Режиссер</label>
                        <input type="text" name="mdirector" required>
                    </div>
                    <div class = 'input-details2'>
                        <label for="mbudget">Бюджет($)</label>
                        <input type="number" name="mbudget" id="mbudget" min='0' max='10000000000'>
                    </div>
                    <div class = 'input-details2' id='poster-wrap'>
                        <label for="mposter" class='hidden'>Постер</label>
                        <input type="file" name="mposter" required class='custom-file-input'>
                    </div>
                    <div class = 'input-details2' id='sumbit-wrap'>  
                        <button name="SubmitButtonN" class='custom-file-input2'>Подтвердить</button>
                    </div>      
                </form>
            </div> 
        </details>
        </div>           
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
            if(isset($_GET['mquery'])){  
                global $mquery;
                $mquery = $_GET['mquery'];}
            if(isset($_GET['fromDate']) && $_GET['fromDate'] != ""){  
                global $fDate;
                $fDate = $_GET['fromDate'];}
            if(isset($_GET['toDate']) && $_GET['toDate'] != ""){  
                global $tDate;
                $tDate = $_GET['toDate'];}
            if(isset($_GET['fromBudget']) && $_GET['fromBudget'] != ""){  
                global $fBudget;
                $fBudget = $_GET['fromBudget'];}
            if(isset($_GET['toBudget']) && $_GET['toBudget'] != ""){  
                global $tBudget;
                $tBudget = $_GET['toBudget'];}   
            //определяем запрос
            $query = "
                SELECT title, date, director, budget, poster
                FROM movies
                WHERE title LIKE '%" . $mquery . "%'
                AND budget >= " . $fBudget . " AND budget <= " . $tBudget . "
                AND date >= " . $fDate . "0101 AND date <= " . $tDate . "1231
                ORDER BY date
            ";
        ?>
        <!-- пагинация и вывод из бд -->   
        <?php
        require_once 'Paginator.class.php'; // включаем страницу, содержащую класс пагинатор, выполняющий пагинацию
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // проверка ошибок
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); // создаем соеденение        
        } catch (mysqli_sql_exception $e) {
            error_log($e->__toString());
        }        
        $limit      = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : 4; // устанавливаем лимит фильмов на странице
        $page       = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1; // первая страница
        $links      = ( isset( $_GET['links'] ) ) ? $_GET['links'] : 2; // количество переходов (в обе стороны)
        $Paginator  = new Paginator($conn, $query); // создаем соеденение и отправляем запрос в бд
        $results    = $Paginator->getData($limit, $page); // получаем результат
        ?>
        <div class='list-of-movies'>
            <ul>    
            <?php
            $uploaddir = "/repository/movie_x7rain/uploads/posters/"; //место куда сохранены постеры  
            try {
                for( $i = 0; $i < count( $results->data ); $i++ ){ //код будет повторяться в зависимости от лимита
                    $budget = '$'; // на случай если не указан бюджет
                    if ($results->data[$i]["budget"] == ''){       
                        $budget = 'не указан';}
                    echo '<div class="movie-line">';
                        echo "<img class='poster-mov' src='" . $uploaddir . $results->data[$i]["poster"] . "' alt='poster' height='200' width='142'>";         
                        echo '<div class="mov-text-field"><li><span class="title">', $results->data[$i]["title"], "</span> <span class='no-wrap'>(", $results->data[$i]["date"], ")</span>, ", $results->data[$i]["director"], ", Бюджет: <span class='no-wrap'><span id='budget" . $i . "'></span>" . $budget . "</span></li></div>"; // собственно вывод списка  
                    echo '</div>';              
                    echo '<script>
                            function divideNumberByPieces(x, delimiter) {
                                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, delimiter || " ");
                            }
                            document.getElementById("budget' . $i . '").innerHTML = divideNumberByPieces(' . $results->data[$i]["budget"] . ');
                    </script>'; 
                    } // с постером
                                                                
            } catch (TypeError) {
                echo "";
            }            
            ?>            
            </ul>
        </div>
    </div> 
    <?php echo $Paginator->createLinks($links, 'paginationLinks', $mquery, $fDate, $tDate, $fBudget, $tBudget); ?>   <!-- ссылки пагинации -->  
         
</body>
</html>                 