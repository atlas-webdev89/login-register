<?php


namespace Middleware;


class Auth
{
    protected $model;
    protected $session;
    
    public function __construct($container)
        {
            $this->model = $container['model'];
            $this->session = $container['session'];
        }

    public function isLogin() {
        //Проверяем авторизован ли пользователь
        if (isset($_SESSION['auth']) && !empty($_SESSION['auth']) && isset($_SESSION['user_id'])) {
            //Проверяем наличие куки с хеш пользователя. Хеш формируется при аутентификации.
            //Если кука есть обновляем ее время. Если нет - делаем запрос к базе для получения значения хеша и добавляем в куку
            //Данная кука используется для автомачиеского входа после перезагрузки браузера. Сессия при закрытие браузера удаляется 
            if (isset($_COOKIE['hash'])) {
                setcookie("hash", $_COOKIE['hash'], time() + TIMEOUT_USER_HASH, '/');
            }else {
                //Получаем значение хеша пользователя из БД              
                if ($hash = $this->model->getHashUser($_SESSION['user_id'])){                   
                    setcookie("hash", $hash['hash'], time() + TIMEOUT_USER_HASH, '/');
                }else {
                    //Возращаем FALSE есть хеша в БД нет. Для повторной аутентификации и формирования хеша
                    return FALSE;
                }                     
            } 
            return $_SESSION['auth'];
        }else {           
            //Проверяем наличие куки с хеш пользователя при отсутствии сессии
            if (isset($_COOKIE['hash']) && !empty($_COOKIE['hash'])) {              
                if ($user = $this->model->getUsers($_COOKIE['hash'])) {                                
                    //Создаем новую сессию для пользователя
                        $this->session->CreateSessionData($user);                           
                    return $_SESSION['auth'];
                }else {                  
                        //Если хешу, хранимому в куки не найден пользователь в БД, удаляем куку и возвращаем FALSE 
                        setcookie("hash", "", time() - 3600, '/');
                    return FALSE;
                }
            }
            return FALSE;
        }
    }
    //Функция проверки ip адреса пользователя и запись значения в сессию если её там нет или не соответствует текущему значению
    protected function IpUserValid ($ip) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            if ($ipAddress == $ip) {                          
                return TRUE;
            }else 
                return FALSE;
        }
}