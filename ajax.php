<?php
// Установка upload_max_filesize на 1 ГБ
ini_set('upload_max_filesize', '1G');

// Также рекомендуется установить post_max_size, чтобы он был больше или равен upload_max_filesize
ini_set('post_max_size', '1G');
ini_set('display_errors', '1');
error_reporting(E_ALL);
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require "DB.php";

/**
 * @return PHPMailer
 * @throws \PHPMailer\PHPMailer\Exception
 */
function getPHPMailer(): PHPMailer // TODO MAKE ANOTHER 'SEND_MAIL' CLASS FOR EXAMPLE
{
    $mail = new PHPMailer;
    $mail->CharSet = 'utf-8';
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'mailbe06.hoster.by';                         // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = DB::getMAIL_SETTINGS()['USERNAME'];
    $mail->Password = DB::getMAIL_SETTINGS()['PASSWORD'];;             // Ваш пароль от почты с которой будут отправляться письма
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    //$mail->SMTPDebug = 2;
    $mail->Port = 465;                                    // TCP port to connect to / этот порт может отличаться у других провайдеров

    $mail->setFrom(DB::getMAIL_SETTINGS()['USERNAME'], 'NOREPLY | ZVEZDO4ET');
    return $mail;
}

switch ($_POST["formName"]) {
    case 'auth':
        if (DB::passwordExist($_POST["password"])) {
            $user = DB::getUserByPassword($_POST["password"]);
            echo json_encode(["auth" => true, "user_id" => (int)$user["id"], "role" => (int)$user["role"], "category" => (int)$user["category"], "isChairman" => (bool)$user["chairman"]]);
        } else {
            echo json_encode(["auth" => false]);
        }
        break;
    case 'checkAuth':
        $id = $_POST["user_id"];
        $password = $_POST["password"];
        $page = $_POST["page"];
        $user = DB::getUserByPassword($password);
        if ($id == (int)$user['id'] && $page == (int)$user["role"] && $role = (int)$user["role"] && $user["role"] != $_POST['originalPath']) {
            echo json_encode(["authority" => true, "role" => $user["role"]]);
            break;
        } else if ($id == (int)DB::getUserByPassword($password)['id'] && $page == -1 && $user["role"] != $_POST['originalPath']) {
            echo json_encode(["authority" => true, "role" => $user["role"]]);
            break;
        } else if ($id == (int)DB::getUserByPassword($password)['id'] && $user["role"] != $_POST['originalPath']) {
            echo json_encode(["authority" => false, "role" => $user["role"]]);
            break;
        } else if ((int)$user["role"] == (int)$_POST['originalPath']) {
            echo json_encode(["authority" => true, "sameOriginal" => true]);
            break;
        } else {
            echo json_encode(["authority" => false]);
            break;
        }
    case 'scoring':
        if (DB::setScore($_POST["participant_id"], $_COOKIE["user_id"], (int)$_POST["odata"] + (int)$_POST["added"], $_POST["comment"])) {
            echo json_encode(["res" => true]);
        } else {
            echo json_encode(["res" => false]);
        }
        break;
    case 'scoring_load':
        echo json_encode(DB::getNextScoringVideo((int)$_COOKIE['user_id']));
        break;
    case 'diploma':
        echo json_encode(DB::getParticipantsForDiplomByUId($_POST['uid']));
        break;
    case 'special':
        echo json_encode(DB::getSpecial($_POST['n']));
        break;
    case 'thnxtoteacher':
        echo json_encode(DB::getInfoForTeacherThnx($_POST['uid']));
        break;
    case 'deleteUser':
        echo json_encode(DB::deleteUser((int)$_POST['userId']));
        break;
    case 'infoUser':
        echo json_encode(DB::getUserById((int)$_POST['userId']));
        break;
    case 'saveEditInfo':
        echo json_encode(DB::saveEditUserInfo((int)$_POST['userId'], $_POST['name'], (int)$_POST['role'], (int)$_POST['category']));
        break;
    case 'addUser':
        echo json_encode(DB::addUser($_POST['name'], (int)$_POST['role'], (int)$_POST['category'], $_POST['password']));
        break;
    case 'makeChairman':
        echo json_encode(DB::makeChairman($_POST['juryId']));
        break;
    case 'unpayParticipant':
        echo json_encode(DB::unpayParticipant((int)$_POST['pid']));
        break;
    case 'payParticipant':
        echo json_encode(DB::payParticipant((int)$_POST['pid']));
        break;
    case 'getUnpaid':
        echo json_encode(DB::getUnpaid((int)$_POST['cid']));
        break;
    case 'updateComment':
        echo json_encode(DB::updateComment($_POST['id'], $_POST['text'], $_POST['commentFieldName']));
        break;
    case 'addParticipant':
        echo json_encode(DB::addParticipant($_POST));
        break;
    case 'uploadFromFileForm':
        echo json_encode(DB::addParticipantsFromFile($_FILES['file']['tmp_name']));
        break;
    case 'getParticipantScores':
        echo json_encode(DB::getParticipantScoresByPID($_POST['pid']));
        break;
    case 'getParticipantsScores':
        if(key_exists("contestId", $_POST)){
            echo json_encode(DB::getParticipantsScores($_POST['contestId']));
            break;
        }
        echo json_encode(DB::getParticipantsScores());
        break;
    case 'getDiplomasToPrint':
        echo json_encode(DB::getDiplomasToPrint($_POST['contestId']));
        break;
    case 'addDiplomaToPrint':
        echo json_encode(DB::addDiplomaToPrint($_POST['pid'], $_POST['place'], $_POST['dtype'], $_POST['t']));
        break;
    case 'getParticipantsInfoForContest':
        echo json_encode(DB::getParticipantsInfoForContest($_POST['contestId']));
        break;
    case 'deleteParticipant':
        echo json_encode(DB::deleteParticipant($_POST['pid']));
        break;
    case 'sendMail':
        $mail = getPHPMailer();
        $mail->isHTML(true);
        $mail->Subject = $_POST['mail_subject'];
        $mail->Body    = $_POST['mail_body']."<div><hr><p><i>Это письмо пришло автоматически.<br>На него отвечать не нужно.</i></p><p>Для связи: <a href='mailto:info@zvezdo4et.com'>info@zvezdo4et.com</a></p><p>С уважением, Zvezdo4et</p></div>";
        $mail->AltBody = '';
        $mail->SMTPDebug = 2;
        if (DB::isMAIL_DEBUG()) {
            $mail->addAddress(DB::getMAIL_DEBUG_ADDRESS());
            $mail->Body .= "<hr><div><pre>THIS MAIL WAS SENT TO \"" . $_POST["mail_send_to"] . "\"</pre></div>";
        } else {
            $mail->addAddress($_POST["mail_send_to"]);
        }
        try {
            $mail->send();
            echo json_encode(["errs" => 0, "res" => "Message sent successfully", "p" => $_POST]);
            break;
        } catch (Exception $e) {
            echo json_encode(["errs" => 1, "res" => "Message could not be sent. Mailer Error: ", $mail->ErrorInfo, "p" => $_POST]);
            break;
        }
        break;
    case 'getJuryResult':
        echo json_encode(DB::getJuryResult());
        break;
    case 'sendJuryResult':
        $f = DB::getJuryResult();
        if (key_exists('flnm', $f)) {
            try {
                $mail = getPHPMailer();
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                echo json_encode(["err" => 1, "res" => $e]);
                break;
            }
            $mail->isHTML(true);
            $mail->Subject = 'Ведомость по жюри';
            $mail->Body = "<div><p>Здравствуйте!</p><p>Ниже прикреплён общий файл-ведомость по отметкам жюри.</p></div>" . "<div><hr><p><i>Это письмо пришло автоматически.<br>На него отвечать не нужно.</i></p><p>С уважением, Zvezdo4et</p></div>";
            $mail->addAttachment($f['pth'] . $f['flnm'], "Сводная ведомость жюри.xlsx");
            $mail->AltBody = '';
            if (DB::isMAIL_DEBUG()) {
                $mail->addAddress(DB::getMAIL_DEBUG_ADDRESS());
                $mail->Body .= "<hr><div><pre>THIS MAIL WAS SENT TO \"" . print_r(DB::getMails(), true) . "\"</pre></div>";
            } else {
                foreach (DB::getMails() as $email) {
                    $mail->addAddress($email);
                }
            }
            try {
                $mail->send();
                echo json_encode(["errs" => 0, "res" => "Message sent successfully", "p" => $_POST]);
                break;
            } catch (Exception $e) {
                echo json_encode(["errs" => 1, "res" => "Message could not be sent. Mailer Error: ", $mail->ErrorInfo, "p" => $_POST]);
                break;
            }
        } else {
            echo json_encode(["errs" => 1, "f" => $f, "res" => $f['err'], "p" => $_POST]);
            break;
        }
        break;
    case 'doMailingChecked':
        try {
            $mail = getPHPMailer();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            echo json_encode(["err" => 1, "res" => $e]);
            break;
        }
        $mail->isHTML(true);
        $mail->Subject = $_POST["subject"];
        $mail->Body = $_POST["body"] . "<div><hr><p><i>Это письмо пришло автоматически.<br>На него отвечать не нужно.</i></p><p>Для связи: <a href='mailto:art293396106@gmail.com'>art293396106@gmail.com</a></p><p>С уважением, Zvezdo4et</p></div>";
        $mail->AltBody = '';
        foreach ($_POST['participants'] as $email) {
            $mail->addAddress($email);
        }

        try {
            $mail->send();
            echo json_encode(["errs" => 0, "mailfrom"=>$mail->Username, "res" => "Message sent successfully", "p" => $_POST, "i" => $mail->Debugoutput = function ($str, $level) {
                global $debug;
                $debug .= "debug level $level; message: $str";
            }]);
            break;
        } catch (Exception $e) {
            echo json_encode(["errs" => 1, "res" => "Message could not be sent. Mailer Error: ", $mail->ErrorInfo, "p" => $_POST]);
            break;
        }
        break;
    case 'doMailing':
        try {
            $mail = getPHPMailer();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            echo json_encode(["err" => 1, "res" => $e]);
            break;
        }
        $mail->isHTML(true);
        $mail->Subject = $_POST["subject"];
        $mail->Body    = $_POST["body"]."<div><hr><p><i>Это письмо пришло автоматически.<br>На него отвечать не нужно.</i></p><p>Для связи: <a href='mailto:info@zvezdo4et.com'>info@zvezdo4et.com</a></p><p>С уважением, Zvezdo4et</p></div>";
        if (isset($_FILES['file'])) {
            $f = $_FILES['file'];
            $mail->addAttachment($f['tmp_name'], $f['name']);
        }
        $mail->AltBody = '';
        if (DB::isMAIL_DEBUG()) {
            $mail->addAddress(DB::getMAIL_DEBUG_ADDRESS());
            $mail->Body .= "<hr><div><pre>THIS MAIL WAS SENT TO \"" . print_r(DB::getMails(), true) . "\"</pre></div>";
        } else {
            foreach (DB::getMails($filters=$_POST["filters"], $contestId=$_POST["contestId"]) as $email) {
                $mail->addBCC($email);
            }
        }
        try {
            $mail->send();
            echo json_encode(["errs" => 0, "res" => "Message sent successfully", "addresses"=>DB::getMails($filters=$_POST["filters"], $contestId=$_POST["contestId"]), "p" => $_POST, "i" => $mail->Debugoutput = function ($str, $level) {
                global $debug;
                $debug .= "debug level $level; message: $str";
            }]);
            break;
        } catch (Exception $e) {
            echo json_encode(["errs" => 1, "res" => "Message could not be sent. Mailer Error: ", $mail->ErrorInfo, "p" => $_POST]);
            break;
        }
        break;
    case 'addSpecialPrizes':
        $prizes = array();
        $scores = array();
        if(!empty($_POST['prizes'])){
            $prizes=$_POST['prizes'];
        }
        if(!empty($_POST['scores'])){
            $scores=$_POST['scores'];
        }
        echo json_encode(DB::addSpecialPrizes($_POST['pid'], $prizes, $scores));
        break;
    case 'getSpecialsById':
        echo json_encode(DB::getSpecialsById((int)$_POST['pid']));
        break;
    case 'getAllSpecials':
        echo json_encode(DB::getAllSpecials());
        break;
    case 'deleteSpecialById':
        echo json_encode(DB::deleteSpecialById((int)$_POST['sid']));
        break;
    case 'addContest':
        echo json_encode(DB::addContest($_POST));
        break;

    case 'updateContest':
        echo json_encode(DB::updateContest($_POST));
        break;

    case 'deleteContest':
        echo json_encode(DB::deleteContest($_POST['id']));
        break;

    case 'getContest':
        echo json_encode(DB::getContest((int)$_POST['id']));
        break;

    case 'getContests':
        if(key_exists("active", $_POST)){
            if($_POST['active']){
                echo json_encode(DB::getContests(true));
            }
            break;
        }
        echo json_encode(DB::getContests());
        break;

    case 'getTeachers':
        echo json_encode(DB::getTeachers($_POST["contestId"]));
        break;

    case 'getParticipantsForRegistration':
        echo json_encode(DB::getParticipantsForRegistration(DB::getActiveContest()['id']));
        break;

    case 'addSignature':
        echo json_encode(DB::addSignature($_POST['participantId'], $_POST['contestId'], $_POST['author'], $_POST['sign']));
        break;
    case 'getSigns':
        echo json_encode(DB::getSigns($_POST['cid']));
        break;
    case 'addShurl':
        echo json_encode(DB::addShortenedURL($_POST['linkName'], $_POST['fullLink'], $_POST['shurl']));
        break;
    case 'getShortenedURLs':
        echo json_encode(DB::getShortenedURLs());
        break;
    case 'formSent':
        echo json_encode(DB::addParticipantFromForm($_POST, $_FILES));
        break;
    case 'changeParticipantValue':
            if (file_exists('notification.json')) {
                $notifications = json_decode(file_get_contents('notification.json'), true);
            }

            // Добавляем новое уведомление
            $notifications[] = [
                'id' => uniqid(),
                'page' => "admin",
                'datetime' => date('Y-m-d H:i:s'),
                'title' => "Изменения в заявке",
                'text' => "Новые изменения в заявке #".$_POST['id'],
                'showed' => 0
            ];

            // Сохраняем обновленный массив в файл
            file_put_contents('notification.json', json_encode($notifications));

        echo json_encode(DB::changeParticipantValue($_POST['id'], $_POST['field'], $_POST['newValue']));
        break;
    case 'changeParticipantValueWithFile':
        echo json_encode(DB::changeParticipantValueWithFile($_POST['id'], $_POST['field'], $_FILES['newFile'], (int)$_POST['genre']));
        break;
    case 'getParticipantsForProgram':
        echo json_encode(DB::getParticipantsForProgram($_POST['contestId']));
        break;
    case 'getParticipantUIdById':
        echo json_encode(DB::getParticipantUIdById($_POST['pid']));
        break;
    case 'getParticipantsInfoByID':
        echo json_encode(DB::getParticipantsInfoByID($_POST['pid']));
        break;
    case 'newDiploma':
        if (file_exists('notification.json')) {
            $notifications = json_decode(file_get_contents('notification.json'), true);
        }
        // Добавляем новое уведомление
        $notifications[] = [
            'id' => uniqid(),
            'page' => "admin",
            'datetime' => date('Y-m-d H:i:s'),
            'title' => "Новый диплом #".$_POST['pid'],
            'text' => "<a target='_blank' href='https://service.zvezdo4et.com/diploma?uid=".$_POST['uid']."'>НА ПЕЧАТЬ</a>",
            'showed' => 0
        ];
        // Сохраняем обновленный массив в файл
        file_put_contents('notification.json', json_encode($notifications));
        break;
    case 'getMedia':
        $p = DB::getMedia($_POST['contestId']);
        echo json_encode(["errs" => 0, "path"=>$p]);
        break;
    case 'downloadMedia':
        $zip = new ZipArchive();
        $zipFileName = match ($_POST["type"]) {
            'music' => "Аудиоряд",
            'video' => "Видеоряд",
            'birth' => "СвОРожд",
            default => "musicFile, videoFile, birthСertificate",
        };
        $zipFileName .= "--" . $_POST['contestId'] . ".zip";
        $type = match ($_POST["type"]) {
            'music' => "musicFile",
            'video' => "videoFile",
            'birth' => "birthСertificate",
            default => "musicFile, videoFile, birthСertificate",
        };

        $files = DB::getTypedFilesOnContest($_POST['type'], $_POST['contestId']);

        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                $filePath = $file[$type];
                if (!empty($filePath) && file_exists($filePath)) {
                    // Получаем basename файла
                    $baseName = basename($filePath);
                    // Создаем новое имя файла с префиксом "ФЕСТ_"
                    $newName = '!_' . $baseName;
                    // Добавляем файл в архив с новым именем
                    $zip->addFile($filePath, $newName);
                }
            }
            $zip->close();
            echo json_encode(["errs" => 0, "href"=>$zipFileName, "files"=>$files, "type"=>$_POST['type'], "cid"=>$_POST['contestId']]);
        } else {
            echo json_encode(["errs" => 1, "text"=>"Ошибка при создании ZIP-архива."]);
        }
        break;

    case "changeNotificationStatus":
        // Указываем путь к файлу
        $filePath = 'notification.json';

// Проверяем, существует ли файл
        if (file_exists($filePath)) {
            // Загружаем существующие уведомления
            $notifications = json_decode(file_get_contents($filePath), true);

            // Перебираем уведомления и обновляем showed для id = 5
            foreach ($notifications as &$notification) {
                if ($notification['id'] === $_POST["notification_id"]) { // Сравниваем как строку
                    $notification['showed'] = 1; // Устанавливаем showed в 1
                    break; // Выходим из цикла после обновления
                }
            }

            // Сохраняем обновленный массив в файл
            file_put_contents($filePath, json_encode($notifications));

            echo json_encode(['status' => 'success', 'message' => 'Поле showed обновлено.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Файл не найден.']);
        }
        break;

    case "saveTableToJSONTempFile":
        $table = $_POST['table'];
        $tableType = $_POST['type'];
        $dateTime = date('Y-m-d_H-i-s');

        $directoryPath = 'tablesTemp';
        $filePath = $directoryPath . '/table-'.$tableType.$dateTime.'-data-'.uniqid().'.json';

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true); // 0755 - права доступа, true - рекурсивное создание
        }

        // Сохраняем JSON-строку в файл
        if (file_put_contents($filePath, $table)) {
            DB::saveTableToJSONTempFile($filePath, $_POST['contestId']);
            echo json_encode(['status' => 'success', 'message' => 'Файл сохранен.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Файл не сохранен.']);
        }
        break;
    case "getLatestTable":
        $table = DB::getLatestTable($_POST['contestId']);
        if (empty($table)) {
            echo json_encode(['status' => '0']);
        } else {
            if (file_exists($table[0]['data'])) {
                // Читаем содержимое файла
                $jsonData = file_get_contents($table[0]['data']);

                // Преобразуем JSON-строку в массив
                $arrayData = json_decode($jsonData, true); // true для получения ассоциативного массива

                // Проверяем на ошибки
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Выводим массив
                    echo json_encode(['status' => '1', 'table' => json_encode($arrayData)]);
                } else {
                    echo json_encode(['status' => '0', 'message'=>"Ошибка декодирования JSON: " . json_last_error_msg()]);

                }
            } else {
                echo json_encode(['status' => '0', 'message'=>"Файл не найден."]);
            }
        }
        break;
    case 'sendTGMessage_':
        $chat_id = $_POST['chat_id'];
        $text = $_POST['text'];

        $botApiToken = '5556454745:AAEB0e1zDDjO7wQU2lTzR7edYV2oou5T21Y';
        $channelId ='your channel id';
        $query = http_build_query([
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode'=> 'html'
        ]);
        $url = "https://api.telegram.org/bot{$botApiToken}/sendMessage?{$query}";

//        $curl = curl_init();
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => $url,
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_CUSTOMREQUEST => 'GET',
//        ));
//        curl_exec($curl);
//        curl_close($curl);
        echo json_encode(file_get_contents($url));
        break;
    case 'sendTGMessage':
        if(key_exists("chat_id", $_POST)){
            if($_POST['chat_id'] != ""){
                $chat_id = $_POST['chat_id'];
            }
        } else {
            $chat_id = "279714843";
        }
        $url = "https://api.telegram.org/bot5556454745:AAEB0e1zDDjO7wQU2lTzR7edYV2oou5T21Y/sendMessage";
        if(key_exists("message", $_POST)){
            $message = $_POST['message'];
        } else {
            echo json_encode(["errs"=> 1, "res"=>"Must be some text in message"]);
            break;
        }
        $data = [
            'chat_id' => $chat_id,
            'text' => $message,
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($url."?chat_id=".$chat_id."&message=".$message);

        if ($result === FALSE) {
            echo json_encode(["errs"=> 1, "res"=>"Error sending message"]);
            break;
        }
        echo json_encode(["errs"=> 0, "res"=>"Message sent successfully!"]);
        break;

    case 'getLoyalCard':
        echo json_encode(DB::getLoyalCard($_POST['id']));
        break;
    case 'addLoyalCard':
//        echo json_encode(DB::addLoyalCard());
        echo    json_encode(["res"=>true]);
        break;


    case 'generatePlacesDoc':
        echo json_encode(DB::generatePlacesDoc($_POST['info']));
        break;


    case 'getDEBUGvalues':
        echo json_encode(DB::getDebugValues());
        break;
    case 'changeDebug':
        echo json_encode(DB::changeDebug(filter_var($_POST['value'], FILTER_VALIDATE_BOOLEAN)));
        break;
    case 'changeMailDebug':
        echo json_encode(DB::changeMailDebug(filter_var($_POST['value'], FILTER_VALIDATE_BOOLEAN)));
        break;
    case 'changeDebugMailAddress':
        echo json_encode(DB::changeDebugMailAddress((string)$_POST['value']));
        break;
    default:
        echo json_encode(["errs" => 1, "res" => false, "p" => $_POST, "g" => $_GET, "s" => $_SERVER, "f" => $_FILES]);
        break;
}