<!-- пагинация -->
    <?php
        class Paginator {
            private $_conn; //соеденение
            private $_limit;
            private $_page;
            private $_query; //запрос
            private $_total;
            public function __construct( $conn, $query ) {   // функция конструкт  
                $this->_conn = $conn;
                $this->_query = $query;
    
                $rs= $this->_conn->query( $this->_query ); //отправляет запрос по соеденению (сделать проверку на ошибки?) !!!
                $this->_total = $rs->num_rows; //получает количество строк в результате запроса $_total
            }
            public function getData( $limit = 10, $page = 1 ) {      // функция извлечения информации из бд  
                $this->_limit   = $limit; //количество строк на странице $_limit
                $this->_page    = $page; //номер страницы $_page;
                if ( $this->_limit == 'all' ) {
                    $query      = $this->_query; // если лимит не установлен весь запрос выведется на одну страницу
                } else {
                    $query      = $this->_query . " LIMIT " . ( ( $this->_page - 1 ) * $this->_limit ) . ", $this->_limit"; 
                } // если лимит установлен то к запросу добавится условие ЛИМИТ с двумя аргументами: 1 - начало отсчета, (номер страницы-1)*лимит; 2-лимит
                $rs             = $this->_conn->query( $query ); // выполняет запрос в бд
                
                while ($row = $rs->fetch_array(MYSQLI_ASSOC)) {                    
                    $results[]  = $row;            //извлекаем ряды из таблицы и записываем их в переменную $results
                }
                $results[] = "";
                $result         = new stdClass(); // Создаем объект для хранения результата
                $result->page   = $this->_page; // номер страницы
                $result->limit  = $this->_limit; // лимит строк на страницу
                $result->total  = $this->_total; // общее количество строк в запросе
                $result->data   = $results; //собственно извлеченная информация

                return $result;    // вовщращаем результат
            }
            public function createLinks( $links, $list_class, $mquery, $fDate, $tDate, $fBudget, $tBudget) { // функция для создания ссылок (запрашивает количество ссылок на странице и класс для CSS стиля)
                if ( $this->_limit == 'all' ) { // если лимит не установлен, необходимости в пагинации нет, функция не выполняется
                    return '';
                }

                $last       = (ceil( $this->_total / $this->_limit ) > 0) ? ceil( $this->_total / $this->_limit ) : 1 ; //вычисляем последнюю страницу округляя в большую сторону результат деления количества рядов на лимит

                $start      = ( ( $this->_page - $links ) > 0 ) ? $this->_page - $links : 1;   // вычисляет первую ссылку после ...
                $end        = ( ( $this->_page + $links ) < $last ) ? $this->_page + $links : $last; // вычисляет последнюю ссылку перед ...

                $html       = '<ul class="' . $list_class . '">'; // создаем список из ссылок

                $class      = ( $this->_page == 1 ) ? "disabled" : ""; // для первой страницы отключаем переход на предыдущую страницу
                $html       .= '<li class="' . $class . '"><a href="?limit=' . $this->_limit . '&page=' . ( $this->_page - 1 ) . '&mquery=' . $mquery . '&fromDate=' . $fDate . '&toDate=' . $tDate . '&fromBudget=' . $fBudget . '&toBudget=' . $tBudget . '">&laquo;</a></li>'; // определеяем << ссылку

                if ( $start > 1 ) {
                    $html   .= '<li><a href="?limit=' . $this->_limit . '&page=1&mquery=' . $mquery . '&fromDate=' . $fDate . '&toDate=' . $tDate . '&fromBudget=' . $fBudget . '&toBudget=' . $tBudget . '">1</a></li>'; // определяем ссылку на первую страницу
                    $html   .= '<li class="disabled"><span>...</span></li>'; // разделяем троеточием
                }

                for ( $i = $start ; $i <= $end; $i++ ) {     // добавляет в список ссылок все ссылки до конечной
                    $class  = ( $this->_page == $i ) ? "active" : ""; // устанавливает класс активной для ссылки на текущую страницу
                    $html   .= '<li class="' . $class . '"><a href="?limit=' . $this->_limit . '&page=' . $i . '&mquery=' . $mquery . '&fromDate=' . $fDate . '&toDate=' . $tDate . '&fromBudget=' . $fBudget . '&toBudget=' . $tBudget . '">' . $i . '</a></li>'; // определяет ссылки до последней
                }

                if ( $end < $last ) {  
                    $html   .= '<li class="disabled"><span>...</span></li>'; // разделяем троеточием
                    $html   .= '<li><a href="?limit=' . $this->_limit . '&page=' . $last . '&mquery=' . $mquery . '&fromDate=' . $fDate . '&toDate=' . $tDate . '&fromBudget=' . $fBudget . '&toBudget=' . $tBudget . '">' . $last . '</a></li>'; //определяем ссылку на последнюю страницу
                }

                $class      = ( $this->_page == $last ) ? "disabled" : ""; // для последней страницы отключаем переход на следующуюю страницу
                $html       .= '<li class="' . $class . '"><a href="?limit=' . $this->_limit . '&page=' . ( $this->_page + 1 ) . '&mquery=' . $mquery . '&fromDate=' . $fDate . '&toDate=' . $tDate . '&fromBudget=' . $fBudget . '&toBudget=' . $tBudget . '">&raquo;</a></li>'; //определеяем >> ссылку

                $html       .= '</ul>'; //закрываем наш список ссылок

                return $html; // возвращаем список ссылок
            }  
        }
    ?>      