<?php
//header("Access-Control-Allow-Origin: http://localhost:8080");
//header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Замените на нужные методы
//header("Access-Control-Allow-Headers: Content-Type, Authorization");
ini_set('upload_max_filesize', '1G');
ini_set('post_max_size', '1G');
require 'vendor/autoload.php';
require 'vendor/phpqrcode/qrlib.php';
require 'CONFIG.php';

use PHPMailer\PHPMailer\PHPMailer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\Wizard;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;


class DB{

    const ROLES = [
        1 => "dev",
        2 => "admin",
        4 => "reg",
        5 => "referee",
        6 => "scene"
    ];
    const ROLES_RUS = [
        1 => "Разработчик",
        2 => "Администратор",
        4 => "Регистратор",
        5 => "Арбитр",
        6 => "Работник сцены"
    ];
    const PAGES = [
        1 => "dev",
        2 => "admin",
        3 => "scoring",
        4 => "registration",
        5 => "arbitr",
        6 => "scene"
    ];
    const GENRES = [
        0 => "Не член жюри",
        1 => "Вокал",
        2 => "Хореография",
        3 => "Театр моды",
        4 => "Театральное искусство",
        5 => "Инструментальное исполнительство",
        6 => "Изобразительное искусство",
    ];
    const GENRES_TECH = [
        1 => "Вокал",
        2 => "Хореография",
        3 => "Театр моды",
        4 => "Театрал. искусство",
        5 => "Инструм. исполнительство",
        6 => "Изобраз. искусство",
    ];
    const CATEGORIES = [
        1 => "Солисты",
        2 => "Малая группа",
        3 => "Ансамбли формейшн",
        4 => "Ансамбли продакшн",
    ];
    const CATEGORIES_FULL = [
        1 => "Солисты",
        2 => "Малая группа (2 - 7 участников)",
        3 => "Ансамбли формейшн (8-24 участников в номере)",
        4 => "Ансамбли  продакшн (25 и более участников в номере)",
    ];
    const AGES = [
        1 => "Группа А: до 6 лет включительно",
        2 => "Группа B: от 7 до 9 лет",
        3 => "Группа С: от 10 до 12 лет",
        4 => "Группа D: от 13 до 15 лет",
        5 => "Группа E: от 16 до 18 лет",
        6 => "Группа F: учащиеся и выпускники музыкальных, специализированных училищ, колледжей, ВУЗов (без возрастных ограничений)",
        7 => "Группа G: любители (без возрастных ограничений)",
        8 => "Группа H: смешанная возрастная группа",
    ];

    const LEVELS = [
        1 => "Начинающий", // (до года заняиий)",
        2 => "Продолжающий", // (свыше года занятий)"
    ];
    const LEVELS_FULL = [
        1 => "Начинающий (до года занятий)",
        2 => "Продолжающий (свыше года занятий)"
    ];
    const NOMINATIONS = [
        1 => [
            1=>"Академический",
            2=>"Народный",
            3=>"Эстрадный",
            4=>"Джазовый",
            5=>"Авторская песня",
            6=>"Песня на родном языке или на языке страны, которую представляет участник",
            7=>"Мировой хит",
            8=>"Песня из мультфильма, кинофильма, мюзикла",
            9=>"Ретро-шлягер",
            10=>"Патриотическая песня / Военно-патриотическая песня"
        ],
        2 => [
            1=>"Детский сюжетно-игровой танец",
            2=>"Народный танец и стилизованный народный танец",
            3=>"Эстрадный танец",
            4=>"Street Dance; Dance Show; Fantasy Show",
            5=>"Танцы с помпонами, мажоретки",
            6=>"Оригинальный жанр (гимнастика, акробатика, спортивно-эстрадный танец, эстетическая гимнастика, художественная гимнастика)",
            7=>"Belly Dance/Oriental (восточный танец)",
            8=>"Современная хореография (модерн, джаз, экспериментальная хореография, контемпорари)",
            9=>"Классический танец",
            10=>"Бальный танец / Latina Dance",
            11=>"Патриотический танец / Военно-патриотический танец",
            12=>"K-POP / Cover Dance",
            13=>"Импровизация / Соло",
            14=>"Номинация по запросу участника"
        ],
        3 => [
            "-1 / 1"=>"Прет-а-порте",
            "-1 / 2"=>"Вечерняя одежда",
            "-1 / 3"=>"Национальный костюм",
            "-1 / 4"=>"Исторический костюм",
            "-1 / 5"=>"Современный костюм",
            "-1 / 6"=>"Фантазийная одежда",
            "-1 / 7"=>"Одежда делового стиля",
            "-1 / 8"=>"Одежда для спорта и отдыха",

            "-2 / 1"=>"Прет-а-порте",
            "-2 / 2"=>"Вечерняя одежда",
            "-2 / 3"=>"Национальный костюм",
            "-2 / 4"=>"Исторический костюм",
            "-2 / 5"=>"Современный костюм",
            "-2 / 6"=>"Фантазийная одежда"
        ],
        4 => [
            1=>"Театр (спектакль музыкальный, драматический, камерный; военно-патриотическая пьеса; театр танца, кукол, теней, театр пластики)",
            2=>"Журналистика/Блогерство",
            3=>"Художественное слово"
        ],
        5 => [
            1=>"Фортепиано",
            2=>"Народные инструменты",
            3=>"Струнно-смычковые инструменты",
            4=>"Духовые инструменты",
            5=>"Смешанный ансамбль/оркестр"
        ],
    ];

    public static function connection()
    {
        $HOST = CONFIG::getHOST();
        return mysqli_connect($HOST['HOST'], $HOST['USERNAME'], $HOST['PASSWORD'], $HOST['DATABASE'], $HOST['PORT']);
    }

    public static function isMAIL_DEBUG(){ // TODO !!IMPORTANT!! REDO IN NEX REVISION
        return CONFIG::isMAIL_DEBUG();
    }

    public static function getMAIL_DEBUG_ADDRESS(){ // TODO !!IMPORTANT!! REDO IN NEX REVISION
        return CONFIG::getMAIL_DEBUG_ADDRESS();
    }

    public static function getMAIL_SETTINGS(){ // TODO !!IMPORTANT!! REDO IN NEX REVISION
        return CONFIG::getMAIL_SETTINGS();
    }

    public static function passwordExist(string $password): bool
    {
        if(mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT `id` FROM `users` WHERE `password` = '" . mysqli_real_escape_string(self::connection(), $password) . "'")))
        {
            return true;
        }
        return false;
    }

    public static function getUserByPassword(string $password): array
    {
        $res = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT * FROM `users` WHERE `password` = '" . mysqli_real_escape_string(self::connection(), $password) . "'"));
        return [
            "id" => $res['id'],
            "name" => $res['name'],
            "role" => $res['role'],
            "category" => $res['category'],
            "chairman" => $res['jury_chairman']
        ];
    }
    public static function getUserById(int $id): array
    {
        $res = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT * FROM `users` WHERE `id` =" . $id));
        return [
            "id" => $res['id'],
            "name" => $res['name'],
            "role" => $res['role'],
            "category" => $res['category'],
            "chairman" => $res['jury_chairman']
        ];
    }

    public static function getUsers(): array
    {
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `users`"), MYSQLI_ASSOC);
    }

    public static function getParticipants(bool $order = null): array
    {
        if($order!=null){
            return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `participants` ORDER BY `genre`, `category`, `age`, `level`, `nomination`"), MYSQLI_ASSOC);
        }
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `participants`"), MYSQLI_ASSOC);

    }

    public static function getJuries(): array
    {
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `users` WHERE `role`=3"), MYSQLI_ASSOC);
    }

    public static function makeChairman(int $juryId): array
    {
        mysqli_query(self::connection(), "
            UPDATE `users`
            SET `jury_chairman` = 0
            WHERE `category` = (
                SELECT `category`
                FROM (
                    SELECT `category`
                    FROM `users`
                    WHERE `id` = ". $juryId ."
                ) AS subquery
            )");
        mysqli_query(self::connection(), "UPDATE `users` SET `jury_chairman` = 1 WHERE `users`.`id` = ".$juryId);
        return ["res" => "ok"];
    }

    public static function getInfoForTeacherThnx(string $uid): array
    {
        $res = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT organizationName, teachersList, contestId FROM participants WHERE uID = '" . $uid . "'"));
        $cres = self::getContest($res["contestId"])["res"][0];
        return [
            "organizationName" => $res['organizationName'],
            "teachers" => $res["teachersList"],
            "thankText" => $cres["thankText"],
        ];
    }

    public static function getTeachers($cID): array
    {
        $q = "SELECT organizationName, teachersList FROM `participants` WHERE `contestId` = " . $cID;
        $res = mysqli_fetch_all(mysqli_query(self::connection(), $q), MYSQLI_ASSOC);
        return $res;
    }

    public static function getSpecial(string $n): array
    {
        list($specialId, $participantId) = explode("_", $n);
        $res = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT s.*, p.id, p.participantName, p.organizationName, p.country, p.city, p.teachersList, p.competitionProgram FROM special_prizes s JOIN participants p ON s.participant_id = p.id WHERE s.id = " . $specialId));
        $teachersInfoListStr = "";
        $teachersList = json_decode($res["teachersList"], true);
        $count = count($teachersList);
        foreach ($teachersList as $index => $item) {
            $position = $item['position'];
            $teachersInfoListStr .= "{$position}: {$item['name']}";

            // Добавляем <hr> если это не последний элемент
            if ($index < $count - 1) {
                $teachersInfoListStr .= "<br>";
            }
        }
        if($participantId == $res['id']){
            return [
                "participantName" => $res['participantName'],
                "organizationName" => $res['organizationName'],
                "country" => $res['country'],
                "city" => $res['city'],
                "teachersList" => $teachersInfoListStr,
                "specialName" => $res['name'],
                "competitionProgram" => $res['competitionProgram']
            ];
        } else {
            return [
                "participantName" => "У вас нет доступа.",
                "organizationName" => "У вас нет доступа.",
                "country" => "У вас нет доступа.",
                "city" => "У вас нет доступа.",
                "teachersList" => "У вас нет доступа.",
                "specialName" => "У вас нет доступа."
            ];
        }

    }

    public static function getParticipantsInfoByUId(string $uid): array
    {
        return mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT * FROM `participants` WHERE `uID` = '" . mysqli_real_escape_string(self::connection(), $uid) ."'"));
    }

    public static function getParticipantsInfoByID(string $pid): array
    {
        return mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT * FROM `participants` WHERE `id` = " . $pid));
    }

    public static function getParticipantUIdById($id)
    {
        return mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT `uID` FROM `participants` WHERE `id` = " . $id));
    }

    public static function getDiplomasToPrint($contestId){
        return mysqli_fetch_all(mysqli_query(self::connection(),
            "SELECT 
            d.participantId,
            d.datetimeOfAdding, 
            d.participantUID,
            d.place,
            p.participantName,
            p.competitionProgram,
            d.printed   
        FROM 
            diplomasToPrint AS d
        JOIN 
            participants AS p ON d.participantId = p.id
        WHERE 
            d.contestId = " . (int)$contestId ."
        ORDER BY d.printed DESC, CASE d.place
        WHEN 'D3' THEN 1
        WHEN 'D2' THEN 2
        WHEN 'D1' THEN 3
        WHEN 'L3' THEN 4
        WHEN 'L2' THEN 5
        WHEN 'L1' THEN 6
        ELSE 7
END;"), MYSQLI_ASSOC);
    }

    public static function addDiplomaToPrint($pid, $place, $dtype, $t): array
    {
        $q = "INSERT INTO `diplomasToPrint`(`participantId`, `participantUID`, `dtype`, `t`, `contestId`, `place`) VALUES (". $pid .", (SELECT `uID` FROM `participants` WHERE `id`=". $pid ."), ". $dtype .", '".$t."', (SELECT `contestId` FROM `participants` WHERE `id`=". $pid ."), '".$place."')";
        if(mysqli_query(self::connection(), "DELETE FROM diplomasToPrint WHERE participantId = ". $pid ." AND dtype = 0")){
            if(mysqli_query(self::connection(), $q)){
                return ["res"=>1];
            }
        }
        return ["res"=>0, "error"=>mysqli_error(self::connection())];

    }
    public static function getParticipantsForDiplomByUId(string $uid): array
    {
        $res = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT * FROM `participants` WHERE `uID` = '" . mysqli_real_escape_string(self::connection(), $uid) ."'"));
        $participant_id = $res['id'];
        $place = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT place FROM `diplomasToPrint` WHERE `participantId` = $participant_id"))['place'];
        $teachersInfoListStr = "";
        $teachersList = json_decode($res["teachersList"], true);
        $count = count($teachersList);
        foreach ($teachersList as $index => $item) {
            $position = $item['position'];
            $teachersInfoListStr .= "{$position}: {$item['name']}";

            // Добавляем <hr> если это не последний элемент
            if ($index < $count - 1) {
                $teachersInfoListStr .= "<br>";
            }
        }


        $nom = "";
        try{
            $nom = self::NOMINATIONS[$res['genre']][$res['nomination']];
        } catch (Exception $e){
            $nom = "";
        }
        return [
            "place" => $place,
            "participantName" => $res['participantName'],
            "organizationName" => $res['organizationName'],
            "country" => $res['country'],
            "city" => $res['city'],
            "competitionProgram" => $res['competitionProgram'],
            "genre" => self::GENRES[$res['genre']],
            "nomination" => $nom,
            "category" => self::CATEGORIES[$res['category']],
            "age" => self::AGES[$res['age']],
            "level" => self::LEVELS[$res['level']],
            "teacherName" => $teachersInfoListStr,
            "participantsAmount" => count(json_decode($res["participantsList"])),
            "participantsList" => json_decode($res["participantsList"]),
            "uid"=>$uid
        ];
    }

    public static function getNextScoringVideo(int $juryId): array
    {
        $_i = self::getParticipantsToScoreByJuryId($juryId);
        if(count($_i) == 0){
            return ["next" => 0];
        }
        $participant_id = $_i[0];
        if($participant_id){
            $res = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT `id`,`participantName`,`organizationName`,`competitionProgram`,`performanceLink`,`country`,`city`,`genre`,`nomination`,`category`,`age`,`level` FROM `participants` WHERE `id` = " .  $participant_id));
            return [
                "participant_id" => $res['id'],
                "participantName" => $res['participantName'],
                "organizationName" => $res['organizationName'],
                "competitionProgram" => $res['competitionProgram'],
                "performanceLink" => $res['performanceLink'],
                "country" => $res['country'],
                "city" => $res['city'],
                "genre" => self::GENRES[$res['genre']],
                "nomination" => $res['nomination'],
                "category" => self::CATEGORIES[$res['category']],
                "age" => self::AGES[$res['age']],
                "level" => self::LEVELS[$res['level']],
                "next" => 1,
                "i" =>$_i
            ];
        } else {
            return ["next" => 0];
        }
    }
    public static function getParticipantsToScoreByJuryId(int $juryId): array
    {
        $res = mysqli_query(self::connection(), "
            SELECT p.id
            FROM participants p
            INNER JOIN users u ON p.genre = u.category
            LEFT JOIN scores s ON p.id = s.participant_id AND s.jury_id = u.id
            WHERE u.id = $juryId
            AND s.id IS NULL;
        ");
        return array_column(mysqli_fetch_all($res, MYSQLI_ASSOC), "id");
    }

    public static function setScore(int $userId, string $juryId, float $score, string $comment=null): bool
    {
        if(mysqli_query(self::connection(), "INSERT INTO `scores`(`participant_id`, `jury_id`, `score`, `comment`) VALUES ($userId, $juryId, $score, '$comment')"))
        {
            return true;
        }
        return false;
    }

    public static function deleteUser(int $user_id): bool
    {
        if(mysqli_query(self::connection(), "DELETE FROM `users` WHERE `id` = $user_id"));
        {
            return true;
        }
        return false;
    }

    public static function saveEditUserInfo(int $userId, string $name, int $role, int $category): bool
    {
        if(mysqli_query(self::connection(), "UPDATE `users` SET `name`='$name',`role`=$role,`category`=$category WHERE `id` = $userId"))
        {
            return true;
        }
        return false;
    }

    public static function addUser(string $name, int $role, int $category, string $password): array
    {
        if(mysqli_query(self::connection(), "INSERT INTO `users`(`name`, `role`, `category`, `password`) VALUES ('$name', $role, $category,'$password')"))
        {
            $lastInsertedId = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT MAX(id) AS last_id FROM users"))['last_id'];
            return [
                "user_id" => $lastInsertedId,
                "res" => true
            ];
        }
        else
        {
            return [
                "user_id" => 0,
                "res" => false,
                "error" => mysqli_error(self::connection())
            ];
        }
    }

    public static function getParticipantScores(string $uID): array
    {

        return  mysqli_fetch_all(mysqli_query(self::connection(), "
            SELECT 
                scores.participant_id,
                scores.jury_id,
                scores.score,
                scores.comment
            FROM scores
            JOIN participants ON scores.participant_id = participants.id
            WHERE participants.uID = '".$uID."'"), MYSQLI_ASSOC);
    }

    public static function getParticipantScoresByPID(string $pID): array{
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `scores` WHERE `participant_id` = ".$pID), MYSQLI_ASSOC);
    }

    public static function getParticipantsScores(int $contestId = null)
    {
        if($contestId){
            $q = "SELECT s.*
                  FROM scores s
                  JOIN participants p ON s.participant_id = p.id
                  WHERE p.contestId = ". $contestId;
            return mysqli_fetch_all(mysqli_query(self::connection(), $q), MYSQLI_ASSOC);
        }
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `scores`"), MYSQLI_ASSOC);
    }

    public static function unpayParticipant(int $pid): array
    {
        if(mysqli_query(self::connection(), "UPDATE `participants` SET `paid`=0 WHERE `id` = " . $pid)){
            return ["res"=>true];
        }
        return ["res"=>false];
    }

    public static function payParticipant(int $pid): array
    {
        if(mysqli_query(self::connection(), "UPDATE `participants` SET `paid`=1 WHERE `id` = " . $pid)){
            return ["res"=>true];
        }
        return ["res"=>false];
    }

    public static function addParticipant(array $p): array
    {
        if(mysqli_query(self::connection(), "
        INSERT INTO `participants` (`email`, `phone`, `hasPersonalDataConsent`, `participantName`,
             `participantInfoList`, `organizationName`, `teacherName`, `competitionProgram`,
             `hasAcceptedCompetitionRules`, `performanceLink`, `authorCredits`, `country`,
             `city`, `genre`, `nomination`, `category`, `age`, `level`, `paid`, `uID`)
        VALUES ('{$p['email']}', '{$p['phone']}', '1', '{$p['name']}',
                '{$p['members']}', '{$p['institution']}', '{$p['instructor']}', '{$p['program']}',
                '1', '{$p['perfomanceLink']}', '{$p['authorCredits']}', '{$p['country']}',
                '{$p['city']}', '{$p['genre']}', '{$p['nomination']}', '{$p['category']}', '{$p['age']}', '{$p['level']}', '{$p['paid']}', '{$p['uID']}')")){
            $lastInsertedId = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT MAX(id) AS last_id FROM participants"))['last_id'];
            return [
                "pid" => $lastInsertedId,
                "res" => true
            ];
        }
        return ["res"=>false];
    }
    public static function deleteParticipant(int $pid): array
    {
        $p = mysqli_fetch_assoc(mysqli_query(self::connection(), "SELECT * FROM `participants` WHERE `id` = " . $pid));



        mysqli_query(self::connection(), "DELETE FROM `participants` WHERE `id` =" . $pid);
        if(mysqli_error(self::connection())==''){
            return ["res"=>true, "p"=>$p];
        } else {
            return ["res"=>false, "details"=>mysqli_error(self::connection())];
        }
    }

    public static function signOut(): void
    {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time()-1000);
                setcookie($name, '', time()-1000, '/');
            }
        }
    }

    public static function addParticipantsFromFile(string $fileName): array
    {
        $participants = self::getParticipants();
        $names = array_column($participants, 'participantName');
        $programs = array_column($participants, 'competitionProgram');
        $q = "INSERT INTO `participants`(`email`, `phone`, `hasPersonalDataConsent`, `participantName`, `participantInfoList`, `organizationName`, `teacherName`, `competitionProgram`, `hasAcceptedCompetitionRules`, `performanceLink`, `authorCredits`, `country`, `city`, `genre`, `nomination`, `category`, `age`, `level`, `paid`, `uID`) VALUES ";
        $arr = [];
        $spreadsheet = IOFactory::load($fileName);
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->getRowIterator(2) as $row) {
            $t = [];
            foreach ($row->getCellIterator() as $cell) {
                array_push($t, $cell->getValue());
            }
            if(in_array($t[9], $names)){
                if(in_array($t[15], $programs)){
                    continue;
                }
            }
            if($t[9]==""){
                break;
            }
            if($t[7]=="Да"){
                $t[7]=1;
            } else {
                $t[7]=0;
            }
            if($t[21]=="Да"){
                $t[21]=1;
            } else {
                $t[21]=0;
            }
            if($t[11]=="Другое.."){
                $t[11]=$t[24];
            }
            $genre = array_search($t[16], DB::GENRES);
            $category = array_search($t[18], DB::CATEGORIES_FULL);
            $age = array_search($t[19], DB::AGES);
            $level = array_search($t[20], DB::LEVELS_FULL);
            $uID = self::generateUniqueString();
            $perfomanceLink = self::getYoutubeVideoId($t[22]);
            $s = "('$t[2]', '+$t[8]', '$t[7]', '$t[9]', '$t[10]', '$t[13]', '$t[14]', '$t[15]', '$t[21]', '$perfomanceLink', '$t[23]', '$t[11]', '$t[12]', '$genre', '$t[17]', '$category', '$age', '$level', '1', '$uID'), ";
            $q .= $s;
            array_push($arr, $t);
        }
        $q = str_replace("\n", mysqli_real_escape_string(self::connection(), "\n"), $q);
        $p = substr($q, 0, strlen($q) - 2);

        if(mysqli_query(self::connection(), $p)){
//        if(true){
            return ["res"=>true, "n"=>$p];
        } else {
            return ["res"=>false, "err"=>mysqli_error(self::connection())];
        }


    }

    private static function generateUniqueString(int $length = 32): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private static function getYoutubeVideoId(string $url)
    {
        $patterns = array(
            '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/i',
            '/^https?:\/\/(?:www\.)?youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/?[^\/\n\s]+\/\?v=|(?:v|e(?:mbed)?)\/|[^\/\n\s]+\?v=)([^#&?\/]{11}).*$/i'
        );

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[7] ?? $matches[1];
            }
        }

        return false;
    }

    public static function getJuryResult(): array
    {
        $jury_arr = [];
        foreach (self::getJuries() as $jury){
            $jury_arr[$jury['category']][] = $jury['name'];
        }

        foreach ($jury_arr as $category=>$name){
            sort($jury_arr[$category]);
        }


        $headerItemsAll = [];
        $headerItemsStart = [
            'ФАМИЛИЯ, ИМЯ участника или Название коллектива',
            'Город (область + город/пгт/деревня и т.д.)',
            'Название студии, учреждения культуры/образования ',
            'ФИО педагога/концертмейстера/хореографа ',
            'Конкурсная программа',
            'Жанр',
            'Уровень подготовки',
            'Номинация',
            'Категория',
            'Возраст',
        ];
        $headerItemsEnd = [
            'Средний бал',
            'Место',
            'Специальный приз',
        ];
        foreach ($jury_arr as $cat => $juries){
            $headerItemsAll[$cat] = $headerItemsStart;
            foreach ($juries as $jury){
                $headerItemsAll[$cat][] = $jury;
            }
            foreach ($headerItemsEnd as $i){
                $headerItemsAll[$cat][] = $i;
            }
        }
        ksort($headerItemsAll);
        $directory = __DIR__.DIRECTORY_SEPARATOR .'generated';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        try {
            // Способ #1. Стили для шапки
            $headerStyles = [
                'font'=>[
                    'color'=>[
                        'rgb' => '000'
                    ],
                    'bold' => false,
                    'size' => 11
                ],
                'fill'=>[
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'F9CB9C'
                    ]
                ],
            ];

            // Создание документа, листа и "писателя"
            $spreadsheet = new Spreadsheet();
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

            $spreadsheet->getDefaultStyle()
                ->getFont()
                ->setName('Times New Roman')
                ->setSize(11);

            foreach (self::GENRES_TECH as $genre) {
                $spreadsheet->createSheet()->setTitle($genre);
            }
            foreach ($headerItemsAll as $cat => $headerItems){
                $sheet = $spreadsheet->setActiveSheetIndexByName(self::GENRES_TECH[$cat]);
                $sheet->fromArray($headerItems); // A1 start
                $row = '1';
                $lastColumn = $spreadsheet->getActiveSheet()->getHighestDataColumn($row);
                $spreadsheet->getActiveSheet()->getStyle('A1:'.$lastColumn.'1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('A1:'.$lastColumn.'1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('A1:'.$lastColumn.'1')->applyFromArray($headerStyles);

            }

            $pdata = [];
            $participants = self::getParticipants(true);
            foreach($participants as $participant){
                $k = [];
                $k[] = $participant['participantName'];
                $k[] = $participant['country'].', '.$participant['city'];
                $k[] = $participant['organizationName'];
                $k[] = $participant['teachersList'];
                $k[] = $participant['competitionProgram'];
                $k[] = self::GENRES[$participant['genre']];
                $k[] = self::LEVELS_FULL[$participant['level']];
                $k[] = $participant['nomination'];
                $k[] = self::CATEGORIES_FULL[$participant['category']];
                $k[] = self::AGES[$participant['age']];

                $k_scores = self::getParticipantScores($participant['uID']);
                $k__scores = array();
                $avg_score_arr = [];
                foreach ($k_scores as $item) {
                    $k__scores[$item['jury_name']] = $item['score'];
                }
                ksort($k__scores);
                foreach ($jury_arr[$participant['genre']] as $jury) {
                    if(array_key_exists($jury, $k__scores)){
                        $k[] = $k__scores[$jury];
                        $avg_score_arr[] = $k__scores[$jury];
                    } else {
                        $k[] = 0;
                        $avg_score_arr[] = 0;
                    }
                }

                $avrg = round(array_sum($avg_score_arr)/count($avg_score_arr), 2);

                if($avrg>=9){
                    $place = "Лауреат I";
                } else if($avrg>=8){
                    $place = "Лауреат II";
                } else if($avrg>=7){
                    $place = "Лауреат III";
                } else if($avrg>=6){
                    $place = "Диплом I";
                } else if($avrg>=5){
                    $place = "Диплом II";
                } else if($avrg>=0){
                    $place = "Диплом III";
                } else {
                    $place = "Диплом III";
                }


                $k[] = $avrg;
                $k[] = $place;
                $k[] = self::getSpecialPrizes($participant['id'], true);
                $pdata[] = $k;
            }

            foreach($pdata as $p){
                $sheet = $spreadsheet->setActiveSheetIndexByName($p[5]);
                $lastRow = $sheet->getHighestRow();
                $newRow = $lastRow+1;
                $sheet->fromArray($p, '-', 'A'.$newRow);
            }

            $sheets = $spreadsheet->getAllSheets();
            foreach($sheets as $sh){
                $lastRow = $sh->getHighestRow();
                $lastColumn = $sh->getHighestColumn();
                for ($row = 1; $row <= 1; $row++) {
                    for ($column = 'A'; $column <= $lastColumn; $column++) {
                        $sh->getStyle($column . $row)
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THICK)
                            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));
                        $sh->getStyle($column . $row)->getAlignment()->setWrapText(true);
                        $sh->getColumnDimension($column)->setWidth(22);
                    }
                }
                $sh->getRowDimension(1)->setRowHeight(55);
                for ($row = 2; $row <= $lastRow; $row++) {
                    for ($column = 'A'; $column <= $lastColumn; $column++) {
                        $sh->getStyle($column . $row)
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN)
                            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));
                    }
                }
            }

            $spreadsheet->removeSheetByIndex(0);
            $fn = uniqid('ведомость_по_жюри__') . '__' . date('Ymd_h-i-s') . '.xlsx';
            $p = $directory.DIRECTORY_SEPARATOR;
            // Сохранение файла
            $writer->save($p . $fn);
            $spreadsheet->disconnectWorksheets();
            return [
                "flnm" => $fn,
                "pth" => $p
            ];
        } catch (Exception $e) {
            $error = date('Y/m/d H:i:s') . ': ' . $e->getMessage() . PHP_EOL;
            return ["err" => $error];
        }

    }

    public static function getMails($filters=null, $contestId=null): array
    {
        $f = "WHERE ";
        if(str_contains("paidOnly", $filters)){
            $f .= "`paid` = 1 ";
        } else {
            $f=0;
        }

        return array_column(mysqli_fetch_all(mysqli_query(self::connection(), "SELECT `email` FROM `participants`".$f."and `contestId`=".$contestId), MYSQLI_ASSOC), 'email');
    }

    public static function sendMassMail(array $addresses, string $subject, string $body)
    {
        $mail = new PHPMailer;
        $mail->CharSet = 'utf-8';
        $mail->isSMTP();
        $mail->Host = 'mailbe06.hoster.by';
        $mail->SMTPAuth = true;
        $mail->Username = CONFIG::getMAIL_SETTINGS()['USERNAME'];
        $mail->Password = CONFIG::getMAIL_SETTINGS()['PASSWORD'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom(CONFIG::getMAIL_SETTINGS()['USERNAME'], 'NOREPLY | ZVEZDO4ET');
        if(CONFIG::isMAIL_DEBUG()){
            $mail->addAddress(CONFIG::getMAIL_DEBUG_ADDRESS());
        } else {
            foreach ($addresses as $address) {
                $mail->addAddress($address);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body."<div><hr><p><i>Это письмо пришло автоматически.<br>На него отвечать не нужно.</i></p><p>Для связи: <a href='mailto:info@zvezdo4et.com'>info@zvezdo4et.com</a></p><p>С уважением, Zvezdo4et</p></div>";
        $mail->AltBody = '';
        try {
            $mail->send();
            return json_encode(["errs"=>0, "res"=>"Message sent successfully", "p"=>$_POST]);
        } catch (Exception $e) {

            return json_encode(["errs"=>1, "res"=>"Message could not be sent. Mailer Error: ", $mail->ErrorInfo, "p"=>$_POST]);
        }
    }

    public static function getContests($active = null): array
    {
        if($active){
            return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT `id`, `name` FROM `contests` WHERE `active`=1 ORDER BY `acceptiongApplicationsFor`"), MYSQLI_ASSOC);
        }
        // return self::connection();
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT `id`, `name` FROM `contests` ORDER BY `acceptiongApplicationsFor`"), MYSQLI_ASSOC);
    }

    public static function getActiveContest(): array
    {
        $contests =  mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `contests` WHERE `active` = 1 ORDER BY `acceptiongApplicationsFor`"), MYSQLI_ASSOC);

        $currentDate = new DateTime(); // Текущая дата
        $closestPastEvent = null;
        $closestDate = null;

        foreach ($contests as $event) {
            $acceptiongApplicationsFor = new DateTime($event['acceptiongApplicationsFor']);

            // Проверяем, прошла ли дата или равна текущей
            if ($acceptiongApplicationsFor <= $currentDate) {
                // Если это первый элемент или дата ближе, чем предыдущая
                if ($closestDate === null || $acceptiongApplicationsFor > $closestDate) {
                    $closestDate = $acceptiongApplicationsFor;
                    $closestPastEvent = $event;
                }
            }
        }


// Вывод результата
        if ($closestPastEvent) {
            return $closestPastEvent;
        } else {
            return ["Нет прошедших событий.\n"];
        }
    }

    public static function getParticipantsByContest(int $contestId): array
    {
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `participants` WHERE `contestId` = ".$contestId), MYSQLI_ASSOC);
    }

    public static function getParticipantsByGenre(int $genreId, int $contestId): array
    {
        if ($contestId){
            return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `participants` WHERE `genre` = ".$genreId." AND `contestId` = ".$contestId." ORDER BY `age`, `level`, `nomination`"), MYSQLI_ASSOC);

        } else {
            return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `participants` WHERE `genre` = ".$genreId." ORDER BY `age`, `level`, `nomination`"), MYSQLI_ASSOC);
        }
    }

    public static function getParticipantsForRegistration($contestId): array
    {
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `participants` WHERE`contestId` = ".$contestId." ORDER BY `organizationName`, `participantName`"), MYSQLI_ASSOC);
    }

    public static function getContestGenres(int $contestId)
    {
        return json_decode(mysqli_fetch_all(mysqli_query(self::connection(), "SELECT `genres` FROM `contests` WHERE `id` = ".$contestId), MYSQLI_ASSOC)[0]['genres'])->genres;
    }

    public static function addSpecialPrizes(int $participant_id, array $prizes, array $scores): array
    {
        $t = [];
        try{
            if(!empty($prizes)){
                foreach ($prizes as $prize) {
                    mysqli_query(self::connection(), "INSERT INTO `special_prizes` (`name`, `participant_id`) VALUES ('".$prize."', ".$participant_id.")");
                }
            }
            if(!empty($scores)){
                foreach ($scores as $score) {
                    if($score['score']==""){
                        continue;
                    }
                    if($score['scoreId']==""){
                        array_push($t, ["type"=>"scoreId is NaN", "sql"=>"INSERT INTO `scores` (`participant_id`, `jury_id`, `score`, `comment`) VALUES (".$participant_id.", '".$score['juryId']."', ".$score['score'].", '".$score['comment']."')"]);
                        mysqli_query(self::connection(), "INSERT INTO `scores` (`participant_id`, `jury_id`, `score`, `comment`) VALUES (".$participant_id.", '".$score['juryId']."', ".$score['score'].", '".$score['comment']."')");
                    } else {
                        array_push($t, ["type"=>"scoreId is NOT NaN", "sql"=>"UPDATE `scores` SET `score`='".$score['score']."',`comment`='".$score['comment']."' WHERE `id`=".$score['scoreId']]);
                        mysqli_query(self::connection(), "UPDATE `scores` SET `score`='".$score['score']."',`comment`='".$score['comment']."' WHERE `id`=".$score['scoreId']);
                    }
                }
            }
            return ["res"=>true, "errs"=>0];

        } catch (Exception $e) {
            return ["errs"=>1, "res"=>$e->getMessage(), "t"=>$t];
        }

    }

    public static function getSpecialPrizes(int $id, $as_string = false): array|string
    {
        $prizes = array_column(mysqli_fetch_all(mysqli_query(self::connection(), "SELECT `name` FROM `special_prizes` WHERE `participant_id` = ".$id), MYSQLI_ASSOC), 'name');
        if($as_string){
            return implode(', ', $prizes);
        } else {
            return $prizes;
        }

    }

    public static function getSpecialsById(int $pid): array
    {
        return ["res" => mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `special_prizes` WHERE `participant_id` = ".$pid), MYSQLI_ASSOC)];
    }

    public static function getAllSpecials(): array
    {
        return ["res" => mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `special_prizes`"), MYSQLI_ASSOC)];
    }

    public static function deleteSpecialById(int $sid): array
    {
        return ["res" => mysqli_query(self::connection(), "DELETE FROM `special_prizes` WHERE `id` = ".$sid)];
    }

    public static function getShortenedURLs(){
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `shortened_urls`"), MYSQLI_ASSOC);

    }

    public static function addShortenedURL($linkName, $fullURL, $shurl): array
    {
        return ["res"=>true, "q"=>mysqli_query(self::connection(), "INSERT INTO `shortened_urls` (`id`, `name`, `fullLink`, `shortenedLink`, `visits`, `active`) VALUES (NULL, '".$linkName."', '".$fullURL."', '".$shurl."', 0, '1')")];
    }
    public static function getFullURL($shurl)
    {
        $l = mysqli_fetch_all(mysqli_query(self::connection(), "SELECT `fullLink`, `visits` FROM `shortened_urls` WHERE `active`=1 AND `shortenedLink` = '".$shurl."'"), MYSQLI_ASSOC);
        if(key_exists(0, $l)){
            $link = $l[0];
        } else {
            return "404";
        }
        mysqli_query(self::connection(), "UPDATE `shortened_urls` SET `visits`=".($link['visits']+1)." WHERE `shortenedLink` = '".$shurl."'");

        return $link['fullLink'];
    }
    public static function upadteShortenedURL($id, $fullURL, $shurl){
        mysqli_query(self::connection(), "UPDATE `shortened_urls` SET `fullLink`=;".$fullURL."', `shortenedLink`= '". $shurl ."' WHERE `id` = '".$id."'");
    }

    public static function addSignature($participantId, $contestId, $author, $sign){

        mysqli_query(self::connection(), "INSERT INTO `signs`(`author`, `participantId`, `contestId`, `sign`) VALUES ('$author', '$participantId', '$contestId', '$sign')");
        return ["res"=>true, "errs"=>0];
    }

    public static function getSigns(int $contest_id): array
    {
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `signs` WHERE `contestId`=".$contest_id), MYSQLI_ASSOC);
    }

    public static function getUnpaid(int $contest_id): array
    {
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT id, paid FROM `participants` WHERE `paid`=0 AND `contestId`=".$contest_id), MYSQLI_ASSOC);
    }

    public static function updateComment(int $id, $text, $commentFieldName)
    {
        return mysqli_query(self::connection(), "UPDATE `participants` SET `".$commentFieldName."`='".$text."' WHERE `id` = ".$id);
    }

    // ADMIN FUNCTIONS
    // TODO MUST WORK FROM "CONFIG"

    public static function changeDebug(bool $param): array
    {
        CONFIG::changeDEBUG($param);
        return ["res" => true];
    }

    public static function changeMailDebug(bool $param): array
    {
        $p = CONFIG::changeMAIL_DEBUG($param);
        return ["res" => true, "p"=>$p];
    }

    public static function changeDebugMailAddress(string $param): array
    {
        CONFIG::changeMAIL_DEBUG_ADDRESS($param);
        return ["res" => true];
    }

    public static function getDebugValues(): array
    {
        return CONFIG::getData();
    }

    public static function addContest(array $contest)
    {
        $genres = implode(',', $contest['genres']);
        return ["res" => mysqli_query(self::connection(), "INSERT INTO `contests`(`name`, `dateOfEvent`, `acceptiongApplicationsFor`, `location`, `stageAreaDescription`, `genres`, `juries`, `payLink`, `regulationsLink`, `offertLink`, `type`, `active`, `specialsTexts`) VALUES ('".$contest['name']."','".$contest['dateOfEvent']."','".$contest['acceptiongApplicationsFor']."','".$contest['location']."','".$contest['stageAreaDescription']."','".$genres."', '".$contest['juries']."', '".$contest['payLink']."','".$contest['regulationsLink']."','".$contest['offertLink']."','0','0', '".$contest['specials']."')")];
    }

    public static function updateContest(array $contest)
    {
        return ["res" => mysqli_query(self::connection(), "UPDATE `contests` SET `name`='".$contest['name']."', `dateOfEvent`='".$contest['dateOfEvent']."', `acceptiongApplicationsFor`='".$contest['acceptiongApplicationsFor']."', `location`='".$contest['location']."', `stageAreaDescription`='".$contest['stageAreaDescription']."', `payLink`='".$contest['payLink']."', `regulationsLink`='".$contest['regulationsLink']."', `offertLink`='".$contest['offertLink']."', `allowShowResults`=".$contest["allowShowResults"].",`juries`='".$contest['juries']."', `specialsTexts`='".$contest['specials']."', `active`=".$contest["active"].", `absenteeParticipation`= ".$contest["absenteeParticipation"]." WHERE `id`=".$contest['id'])];
    }

    public static function deleteContest($id)
    {
        return ["res" => mysqli_query(self::connection(), "DELETE FROM `contests` WHERE `id`=".$id)];
    }

    public static function getContest(int $id): array
    {
        return ["res" => mysqli_fetch_all(mysqli_query(self::connection(), "SELECT * FROM `contests` WHERE `id` = ".$id), MYSQLI_ASSOC)];
    }

    public static function getContestByUID(string $uID)
    {
        return mysqli_fetch_all(mysqli_query(self::connection(), "SELECT contests.* FROM contests JOIN participants ON contests.id = participants.contestId WHERE participants.uID ='".$uID."'"), MYSQLI_ASSOC)[0];
    }

    public static function addParticipantFromForm(array $post, array $files): array
    {
        $farr = [];
        foreach ($files as $fname => $file) {
            $dname = match ($fname) {
                'musicFile' => "Аудиоряд",
                'videoFile' => "Видеоряд",
                'payCheckFile' => "Чеки Оплаты",
                'certificatePhoto' => "Фото Сертификатов на Скидку",
                'birthСertificate' => "Свидетельства о Рождении",
                default => $fname,
            };
            $uploadDir = 'participantUploads/' . $dname . '/' . self::GENRES_TECH[$post["genre"]] . '/'; // Папка для загрузки
            $desiredFileName = $post['contestId']. '__' .uniqid($post['contestId'],true) . "." . self::getFileExtension($file['tmp_name']); // Задайте желаемое имя файла
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Получаем временное имя загруженного файла
            $tempName = $file["tmp_name"];

            // Полный путь для сохранения файла
            $targetFilePath = $uploadDir . $desiredFileName;

            // Перемещение загруженного файла в целевую папку
            move_uploaded_file($tempName, $targetFilePath);
            $farr[$fname] = $targetFilePath;
        }
        foreach($post as $key => $value){
            $post[$key] = mysqli_real_escape_string(self::connection(), $value);
        }
        $sql_string = "INSERT INTO `participants`(`participantName`, `participantsList`, `email`,
                           `phone`, `organizationName`, `teachersList`, `country`, `city`, `competitionProgram`, `duration`, `authorCredits`,
                           `musicFile`, `videoFile`, `genre`, `nomination`, `nominationRequested`, `category`, `age`, `level`, `comment`, `personsInNumber`,
                           `personsAtAll`, `payCheckFile`, `certificatePhoto`, `certificateCode`, `birthСertificate`, `confirmRules`,
                           `confirmPersonalDataProcessing`, `confirmPrivacyPolicy`, `confirmcookie`, `uID`,
                           `contestId`, `deviceType`, `addressForPrizes`, `neededPrizes`, `typeOfParticipation`) VALUES ('" . $post['participantName'] . "','" . $post['participantsList'] . "','" . $post['email'] . "','" . $post['phone'] . "','" . $post['organizationName'] . "','" . $post['teachersList'] . "',
                                                '" . $post['country'] . "','" . $post['city'] . "','" . $post['competitionProgram'] . "','" . $post['duration'] . "','" . $post['authorCredits'] . "','" . ($farr['musicFile'] ?? "") . "',
                                                '" . ($farr['videoFile'] ?? "") . "','" . $post['genre'] . "','" . $post['nomination'] . "','" . $post['nominationRequested'] . "','" . $post['category'] . "','" . $post['age'] . "','" . $post['level'] . "',
                                                '" . $post['comment'] . "','" . $post['personsInNumber'] . "','" . $post['personsAtAll'] . "','" . ($farr['payCheckFile'] ?? "") . "','" . ($farr['certificatePhoto'] ?? "") . "','" . $post['certificateCode'] . "',
                                                '" . ($farr['birthСertificate'] ?? "") . "','" . $post['confirmRules'] . "','" . $post['confirmPersonalDataProcessing'] . "','" . $post['confirmPrivacyPolicy'] . "','" . $post['confirmcookie'] . "','" . $post['uID'] . "',
                                                '" . $post['contestId'] . "', '" . $post['deviceType'] . "', '" . $post['addressForPrizes'] . "', " . $post['neededPrizes'] . ", " . $post['typeOfParticipation'] . ")";
        $mysql_res = mysqli_query(self::connection(), $sql_string);

        return ["res" => $mysql_res];

    }
    public static function changeParticipantValue(mixed $id, mixed $field, mixed $newValue=null): array
    {
        if(empty($newValue)){
            return ["res" => mysqli_query(self::connection(), "UPDATE `participants` SET `".$field."' WHERE `id`= ".$id)];
        }
        return ["res" => mysqli_query(self::connection(), "UPDATE `participants` SET `".$field."`='".$newValue."' WHERE `id`= ".$id)];
    }

    public static function changeParticipantValueWithFile(mixed $id, mixed $field, mixed $newFile, mixed $genre)
    {
        $dname = match ($field) {
            'musicFile' => "Аудиоряд",
            'videoFile' => "Видеоряд",
            'payCheckFile' => "Чеки Оплаты",
            'certificatePhoto' => "Фото Сертификатов на Скидку",
            'birthСertificate' => "Свидетельства о Рождении",
            default => $field,
        };
        $uploadDir = 'participantUploads/' . $dname . '/' . self::GENRES_TECH[$genre] . '/'; // Папка для загрузки
        $desiredFileName = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F]/', ' ', $newFile["name"]); // Задайте желаемое имя файла
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $tempName = $newFile["tmp_name"];
        $targetFilePath = $uploadDir . $desiredFileName;
        move_uploaded_file($tempName, $targetFilePath);
        return ["res"=>mysqli_query(self::connection(), "UPDATE `participants` SET `".$field."`='".$targetFilePath."' WHERE `id`= ".$id)];
    }

    public static function getParticipantsForProgram(mixed $contestId): array
    {
        $q = "SELECT `id`, `participantName`, `phone`, `organizationName`, `teachersList`, `participantsList`, `country`, `city`, `competitionProgram`, `duration`, `authorCredits`, `genre`, `nomination`, `nominationRequested`, `category`, `age`, `level`, `comment`, `personsInNumber`, `personsAtAll`, `musicFile`, `videoFile` FROM `participants` WHERE `contestId`= ".(int) $contestId." ORDER BY FIELD(`genre`, 1, 4, 3, 2), `category`, `nomination`, `level`, `age` ";
        return mysqli_fetch_all(mysqli_query(self::connection(), $q), MYSQLI_ASSOC);
    }

    public static function getJSONArray($array){
        if (defined("self::$array")) {
            return constant("self::$array");
        } else {
            return [0 => 0];
        }
    }

    public static function saveTableToJSONTempFile($filePath, $contestId): void
    {
        mysqli_query(self::connection(), "INSERT INTO `tablesData`(`contestId`, `data`) VALUES (".$contestId.",'".$filePath."')");
    }

    public static function getLatestTable($contestId): array
    {
        $result = mysqli_query(self::connection(), "SELECT * FROM `tablesData` WHERE contestId=".$contestId." order by datetime desc limit 1");
        return mysqli_fetch_all($result, MYSQLI_ASSOC);

    }

    public static function getTypedFilesOnContest(string $type, int $contestId): array
    {
        $type = match ($type) {
            'music' => "musicFile",
            'video' => "videoFile",
            'birth' => "birthСertificate",
            default => "musicFile, videoFile, birthСertificate",
        };
        $result = mysqli_query(self::connection(), "SELECT id, $type FROM participants WHERE contestId=$contestId");
        $files = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $files[] = ['id'=>$row['id'], $type => $row[$type]];
            }
            $filteredFiles = array_filter($files, function($file) use ($type) {
                return !empty($file[$type]);
            });
            $filteredFiles = array_values($filteredFiles);
            return $filteredFiles;
        } else return [];
    }


    public static function deleteUnexistedFiles(){
        $q = mysqli_fetch_all(mysqli_query(self::connection(), "SELECT `musicFile`, `videoFile`, `payCheckFile`, `certificatePhoto`, `birthСertificate` FROM `participants`;"), MYSQLI_ASSOC);
        $filesInDB = [];
        foreach ($q as $i) {
            foreach ($i as $item){
                array_push($filesInDB, $item);
            }
        }
        $filesInDB = array_filter($filesInDB);
        echo DIRECTORY_SEPARATOR;
        $search = ["/","\\"];
        $replace = DIRECTORY_SEPARATOR;
        $filesToKeep = array_map(function($str) use ($search, $replace) {
            return str_replace($search, $replace, $str);
        }, $filesInDB);
        $directory = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."participantUploads";
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }
        foreach ($files as $file) {
            if (!in_array(str_replace($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR, "", $file), $filesToKeep)) {
                unlink($file);
                echo "Удален файл: " . $file . "<br>";
            }
        }

    }

    public static function getJurySheetForMarks($contestId)
    {
        $contest = self::getContest($contestId)['res'][0];
        $juries = json_decode($contest['juries'], true);
        $table = json_decode(file_get_contents(self::getLatestTable($contestId)[0]['data']), true);
        $headerItemsStart = [
            '№',
            'ФАМИЛИЯ, ИМЯ участника или Название коллектива',
            'Страна, город',
            'Название студии, учреждения',
//            'ФИО педагога',
            'Конкурсная программа',
            'Номинация',
            'Возраст',
            'Категория',
            'Уровень подготовки',
            'Отметка',
            'Комментарий',
        ];

        $spreadsheet = new Spreadsheet();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('Times New Roman')
            ->setSize(20);

        foreach ($juries as $jury) {
            $spreadsheet->createSheet() ->setTitle(explode(' ', $jury['name'])[1].'. '.self::GENRES_TECH[(int) $jury['genre']]);
        }

        $headerStyles = [
            'font'=>[
                'color'=>[
                    'rgb' => '000'
                ],
                'bold' => true,
                'size' => 20
            ],
            'fill'=>[
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'F9CB9C'
                ]
            ],
        ];

        foreach ($juries as $jury){
            $sheet = $spreadsheet->setActiveSheetIndexByName(explode(' ', $jury['name'])[1].'. '.self::GENRES_TECH[(int) $jury['genre']]);
            $sheet->fromArray($headerItemsStart); // A1 start
            $row = '1';
            $lastColumn = $spreadsheet->getActiveSheet()->getHighestDataColumn($row);
            $spreadsheet->getActiveSheet()->getStyle('A1:'.$lastColumn.'1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('A1:'.$lastColumn.'1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('A1:'.$lastColumn.'1')->applyFromArray($headerStyles);

        }


        foreach ($juries as $jury){
            $parrall = array();
            foreach($table as $index=>$p){

                if($index!='contestId' && $p['type']=='array'){
                    $r = $p['values']['row'];
                    $p = self::getParticipantsInfoByUId(self::getParticipantUIdById($p['values']['id'])['uID']);
                    if($p['genre'] == $jury['genre']){

                        $parr = [];
                        $parr[] = $r;
                        $parr[] = $p['participantName'];
                        $parr[] = $p['country'].', '.$p['city'];
                        $parr[] = $p['organizationName'];
                        $parr[] = $p['competitionProgram'];
                        $parr[] = self::NOMINATIONS[(int) $p['genre']][$p['nomination']];
                        $parr[] = self::AGES[(int) $p['age']];
                        $parr[] = self::CATEGORIES_FULL[(int) $p['category']];
                        $parr[] = self::LEVELS[(int)$p['level']];
                        $parr[] = "";
                        $parr[] = "";
                        $parrall[] = $parr;
                    }

                }
            }
            $nominationMemory = "";
            foreach ($parrall as $parralldata){
                $sheet = $spreadsheet->setActiveSheetIndexByName(explode(' ', $jury['name'])[1].'. '.self::GENRES_TECH[(int) $jury['genre']]);
                $lastRow = $sheet->getHighestRow();
                $newRow = $lastRow+1;
                if($parralldata[5] . ' / ' . $parralldata[6] .  ' / '.$parralldata[8] != $nominationMemory){
                    $nominationMemory = $parralldata[5] . ' / ' . $parralldata[6] . ' / ' . $parralldata[8];
                    $sheet->setCellValue("A".$newRow, $nominationMemory);
                    $sheet->getStyle("A".$newRow)->applyFromArray([
                        'font'=>[
                            'color'=>[
                                'rgb' => '000'
                            ],
                            'bold' => true,
                            'size' => 20
                        ],
                        'fill'=>[
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => '00B4F0'
                            ]
                        ],
                    ]);
                    $sheet->mergeCells("A".$newRow.":K".$newRow);

                    $newRow += 1;
                }
                $sheet->fromArray($parralldata, '-', 'A'.$newRow);
            }
        }

        $sheets = $spreadsheet->getAllSheets();
        foreach($sheets as $sh){
            $sh->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sh->getPageSetup()
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $sh->getPageSetup()->setFitToWidth(1);
            $sh->getPageSetup()->setFitToHeight(0);
//            $sh->getPageMargins()->
//            $sh->getPageMargins()->
//            $sh->getPageMargins()->
//            $sh->getPageMargins()->
            $sh->getHeaderFooter()
                ->setOddFooter('&L'.$sh->getTitle().'&C'.'&D'.'&R&P / &N');
            $sh->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
            $sh->getStyle('B1:J'.$sh->getHighestRow())
                ->getAlignment()->setWrapText(true);
            $sh->getStyle('A2:A'.$sh->getHighestRow())
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sh->getStyle('A2:A'.$sh->getHighestRow())
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sh->getStyle('B2:J'.$sh->getHighestRow())
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $lastRow = $sh->getHighestRow();
            $lastColumn = $sh->getHighestColumn();
            for ($row = 1; $row <= 1; $row++) {
                for ($column = 'A'; $column <= $lastColumn; $column++) {
                    $sh->getStyle($column . $row)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THICK)
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));
                    $sh->getStyle($column . $row)->getAlignment()->setWrapText(true);
                    $sh->getColumnDimension($column)->setWidth(22);
                }

            }
            $sh->getRowDimension(1)->setRowHeight(100);
            for ($row = 2; $row <= $lastRow; $row++) {
                for ($column = 'A'; $column <= $lastColumn; $column++) {
                    $sh->getStyle($column . $row)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));
                }

                if(!preg_match('/[а-яА-Я]/', $sh->getCell('A'.$row)->getValue())){
                    $sh->getRowDimension($row)->setRowHeight(160);
                } else {
                    $sh->getRowDimension($row)->setRowHeight(40);

                }
            }
        }

        $spreadsheet->removeSheetByIndex(0);


        $fn = uniqid('протокол_жюри') . '__' . date('Ymd_h-i-s') . '.xlsx';
        $p = 'generated'.DIRECTORY_SEPARATOR;
        // Сохранение файла
        $writer->save($p . $fn);
        $spreadsheet->disconnectWorksheets();
        return json_encode(["res"=> true]);

        try {
            // Способ #1. Стили для шапки



            $pdata = [];
            $participants = self::getParticipants(true);
            foreach($participants as $participant){
                $k = [];
                $k[] = $participant['participantName'];
                $k[] = $participant['country'].', '.$participant['city'];
                $k[] = $participant['organizationName'];
                $k[] = $participant['teachersList'];
                $k[] = $participant['competitionProgram'];
                $k[] = self::GENRES[$participant['genre']];
                $k[] = self::LEVELS_FULL[$participant['level']];
                $k[] = $participant['nomination'];
                $k[] = self::CATEGORIES_FULL[$participant['category']];
                $k[] = self::AGES[$participant['age']];

                $k_scores = self::getParticipantScores($participant['uID']);
                $k__scores = array();
                $avg_score_arr = [];
                foreach ($k_scores as $item) {
                    $k__scores[$item['jury_name']] = $item['score'];
                }
                ksort($k__scores);
                foreach ($jury_arr[$participant['genre']] as $jury) {
                    if(array_key_exists($jury, $k__scores)){
                        $k[] = $k__scores[$jury];
                        $avg_score_arr[] = $k__scores[$jury];
                    } else {
                        $k[] = 0;
                        $avg_score_arr[] = 0;
                    }
                }

                $avrg = round(array_sum($avg_score_arr)/count($avg_score_arr), 2);

                if($avrg>=9){
                    $place = "Лауреат I";
                } else if($avrg>=8){
                    $place = "Лауреат II";
                } else if($avrg>=7){
                    $place = "Лауреат III";
                } else if($avrg>=6){
                    $place = "Диплом I";
                } else if($avrg>=5){
                    $place = "Диплом II";
                } else if($avrg>=0){
                    $place = "Диплом III";
                } else {
                    $place = "Диплом III";
                }


                $k[] = $avrg;
                $k[] = $place;
                $k[] = self::getSpecialPrizes($participant['id'], true);
                $pdata[] = $k;
            }

            foreach($pdata as $p){
                $sheet = $spreadsheet->setActiveSheetIndexByName($p[5]);
                $lastRow = $sheet->getHighestRow();
                $newRow = $lastRow+1;
                $sheet->fromArray($p, '-', 'A'.$newRow);
            }

            $sheets = $spreadsheet->getAllSheets();
            foreach($sheets as $sh){
                $lastRow = $sh->getHighestRow();
                $lastColumn = $sh->getHighestColumn();
                for ($row = 1; $row <= 1; $row++) {
                    for ($column = 'A'; $column <= $lastColumn; $column++) {
                        $sh->getStyle($column . $row)
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THICK)
                            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));
                        $sh->getStyle($column . $row)->getAlignment()->setWrapText(true);
                        $sh->getColumnDimension($column)->setWidth(22);
                    }
                }
                $sh->getRowDimension(1)->setRowHeight(55);
                for ($row = 2; $row <= $lastRow; $row++) {
                    for ($column = 'A'; $column <= $lastColumn; $column++) {
                        $sh->getStyle($column . $row)
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN)
                            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));
                    }
                }
            }

            $spreadsheet->removeSheetByIndex(0);
            $fn = uniqid('ведомость_по_жюри__') . '__' . date('Ymd_h-i-s') . '.xlsx';
            $p = $directory.DIRECTORY_SEPARATOR;
            // Сохранение файла
            $writer->save($p . $fn);
            $spreadsheet->disconnectWorksheets();
            return [
                "flnm" => $fn,
                "pth" => $p
            ];
        } catch (Exception $e) {
            $error = date('Y/m/d H:i:s') . ': ' . $e->getMessage() . PHP_EOL;
            return ["err" => $error];
        }
    }

    public static function printArr(bool|array|null $arr)
    {
        echo "<pre style='background: #d1d1d1;
    font-size: 8pt;
    border: 2px solid #9c9c9c;
    border-radius: 5px;
    padding: 5px;'>";
        print_r($arr);
        echo "</pre>";
    }

    public static function getFileExtension($filePath): bool|string
    {
        // Проверяем, существует ли файл
        if (!file_exists($filePath)) {
            return false;
        }

        // Получаем MIME-тип файла
        $mimeType = mime_content_type($filePath);

        // Определяем расширение на основе MIME-типа
        switch ($mimeType) {
            case 'image/jpeg':
                return 'jpg';
            case 'image/png':
                return 'png';
            case 'image/gif':
                return 'gif';
            case 'text/plain':
                return 'txt';
            case 'application/pdf':
                return 'pdf';
            case 'audio/mpeg':
                return 'mp3';
            case 'audio/x-wav':
                return 'wav';
            case 'video/mp4':
                return 'mp4';
            // Добавьте другие MIME-типы и их расширения по мере необходимости
            default:
                return explode('/', $mimeType)[1]; // Неизвестный тип
        }
    }

    public static function copyAndRenameFiles($files, $targetDir): void
    {
        try {
            foreach ($files as $index => $row) {
                if (!empty($row["filePath"]) && file_exists($row["filePath"])) {
                    $fileExtension = pathinfo($row["filePath"], PATHINFO_EXTENSION);
                    $newFileName = $index . '-' . $row["participantId"] . '-' . preg_replace('/[<>\:"\/\\\\|?*]/', ' ', $row["competitionProgram"]) . '.' . $fileExtension;
                    $newFilePath = $targetDir . $newFileName;
                    if(file_exists($row["filePath"])){
                        copy($row["filePath"], $newFilePath);
                    }
//                    echo "Скопирован: $filePath в $newFilePath\n";
                } else {
//                    echo "Файл " . $filePath . " не найден<br>";
                }
            }
        } catch(Exception $e) {
            echo $e->getMessage();
        }

    }

    public static function getParticipantsInfoForContest($contestId){
        if(is_numeric($contestId)){
            $genreCount = [0, 0, 0, 0, 0, 0];
            $countOneParticipant = 0;
            $countTwoParticipants = 0;
            $countThreeOrMoreParticipants = 0;
            $totalParticipants = 0;
            $uniqueParticipants = [];
            $uniqueTeachers = [];
            $participants = mysqli_fetch_all(mysqli_query(self::connection(), "SELECT id, participantName, participantsList, organizationName, teachersList, genre, nomination FROM `participants` WHERE contestId=".$contestId), MYSQLI_ASSOC);
            foreach ($participants as $item) {
                $genre = $item['genre'];
                if (isset($genreCount[$genre])) {
                    $genreCount[$genre]++;
                } else {
                    $genreCount[$genre] = 1;
                }
            }
            foreach ($participants as $item) {
                $participantsInRow = json_decode($item['participantsList'], true); // Декодируем JSON
                $numParticipants = count($participantsInRow); // Считаем количество участников

                // Увеличиваем соответствующий счетчик
                if ($numParticipants == 1) {
                    $countOneParticipant=$countOneParticipant+1;
                } elseif ($numParticipants == 2) {
                    $countTwoParticipants=$countTwoParticipants+1;
                } elseif ($numParticipants >= 3) {
                    $countThreeOrMoreParticipants=$countThreeOrMoreParticipants+1;
                }
            }

            // Подсчет участников
            foreach ($participants as $item) {
                $participantsInRow = json_decode($item['participantsList'], true); // Декодируем JSON
                $totalParticipants += count($participantsInRow); // Увеличиваем общее количество участников

                // Уникальные участники (с учетом имени, даты рождения и организации)
                foreach ($participantsInRow as $participant) {
                    $key = $participant['name'] . '|' . $participant['date'] . '|' . $item['organizationName'];
                    $uniqueParticipants[$key] = true; // Используем ассоциативный массив для уникальности
                }
            }

            // Подсчет уникальных педагогов
            foreach ($participants as $item) {
                $teachers = json_decode($item['teachersList'], true); // Декодируем JSON

                foreach ($teachers as $teacher) {
                    // Создаем уникальный ключ для каждого педагога
                    $key = trim($teacher['name']) . '|' . $item['organizationName'];
                    $uniqueTeachers[$key] = true; // Используем ассоциативный массив для уникальности
                }
            }
            $uniqueTCount = count($uniqueTeachers);
            $uniqueCount = count($uniqueParticipants);

            return ["success"=>1, "pAmount"=> count($participants), "pAmountVokal"=>$genreCount[1], "pAmountHoreograf"=>$genreCount[2], "pAmountTeatr"=>$genreCount[4], "pAmountTeatrMod"=>$genreCount[3], "pAmountInstrumental"=>$genreCount[5], "pAmountSolo"=>$countOneParticipant, "pAmountDuet"=>$countTwoParticipants, "pAmountGrupp"=>$countThreeOrMoreParticipants, "pAmountParticipants"=>$totalParticipants, "pAmountUniqParticipants"=>$uniqueCount, "tAmountUniq"=>$uniqueTCount];
        } else {
            return ["success"=>0];
        }
    }

    public static function getMedia($contestId, $kind, $part=null): string
    {
        $baseDir = 'participantUploads/mediaToDownload/';
        $baseDirZip = 'participantUploads/mediaToDownload(' . uniqid() . ').zip';

        $audioDir = $baseDir . 'Аудио/';
        $videoDir = $baseDir . 'Видео/';
        // Создание папок, если их нет
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        if (!file_exists($audioDir)) {
            mkdir($audioDir, 0777, true);
        }
        if (!file_exists($videoDir)) {
            mkdir($videoDir, 0777, true);
        }

        $table = json_decode(file_get_contents(self::getLatestTable($contestId)[0]['data']), true);


        $files = array();
        foreach($table as $index=>$item){
            if($index!='contestId' && $item['type']=='array'){
                $files['audio'][$item['values']['row']] = ["filePath"=>$item['values']['musicFile'], "participantId"=>$item['values']['id'], "competitionProgram"=>$item['values']['competitionProgram']];
                $files['video'][$item['values']['row']] = ["filePath"=>$item['values']['videoFile'], "participantId"=>$item['values']['id'], "competitionProgram"=>$item['values']['competitionProgram']];
            }
        }
        if($kind=="music"){
            DB::printArr($_GET);
            if($part){
                if($part>4){
                    return "it is possible to split for 4 parts max";
                }
                $length = count($files['audio']);
                $partSize = ceil($length / 3); // округляем в большую сторону для нечетного количества элементов
                $partArray = array_slice($files['audio'], $part * $partSize, $partSize);
            DB::printArr($files['audio']);
                self::copyAndRenameFiles($partArray, $audioDir);
            } else {
                self::copyAndRenameFiles($files['audio'], $audioDir);
            }
            return $audioDir;


        } else if($kind=="video"){
            if($part){
                if($part>4){
                    return "it is possible to split for 4 parts max";
                }
                $length = count($files['video']);
                $partSize = ceil($length / 4); // округляем в большую сторону для нечетного количества элементов
                $partArray = array_slice($files['video'], $part * $partSize, $partSize);
                self::copyAndRenameFiles($partArray, $videoDir);
            }
            self::copyAndRenameFiles($files['video'], $videoDir);
            return $videoDir;
        } else {
            return "undefined 'kind'";
        }

//        $zip = new ZipArchive();
//
//        if ($zip->open($baseDirZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
//            // Рекурсивная функция для добавления файлов в ZIP
//            $files = new RecursiveIteratorIterator(
//                new RecursiveDirectoryIterator($baseDir),
//                RecursiveIteratorIterator::LEAVES_ONLY
//            );
//
//            foreach ($files as $name => $file) {
//
//                // Пропускаем директории (только файлы)
//                if (!$file->isDir()) {
//                    // Получаем относительный путь к файлу
//                    $filePath = $file->getRealPath();
//                    $relativePath = substr($filePath, strlen($baseDir));
//
//                    // Добавляем файл в ZIP
//                    $zip->addFile($filePath, $relativePath);
//                }
//            }
//
//            $zip->close();
//            return $baseDirZip;
//        } else {
//            return "null";
//        }
    }

    public static function getAllLoyalCards(): array
    {
        $cards_ = mysqli_fetch_all(mysqli_query(self::connection(), "select * from loyal_cards"), MYSQLI_ASSOC);
        $result = [];
        foreach ($cards_ as $item) {
            $result[$item['id']] = $item;
        }
        return $result;


    }

    public static function getLoyalCard(int $id): bool|array|null
    {
        return mysqli_fetch_assoc(mysqli_query(self::connection(), "select * from loyal_cards where id=".$id));
    }

    public static function addLoyalCard()
    {
        mysqli_query(self::connection(), "INSERT INTO `loyal_cards` (`participantName`, `organizationName`, `city`, `issueDate`, `history`) VALUES ('ИМЯ УЧАСТНИКА', 'ШКОЛА','ГОРОД','".(date('Y-m-d'))."','{}');");
        $result = mysqli_query(self::connection(), "SELECT max(id) as max_id FROM loyal_cards");
        return mysqli_fetch_assoc($result)['max_id'];
    }

    public static function removeLoyalCard(int $id): bool
    {
        mysqli_query(self::connection(), "DELETE FROM `loyal_cards` WHERE id = " . $id);
        if(mysqli_affected_rows(self::connection()) != 1){
            return false;
        }
        return true;

    }

    public static function editLoyalCard(int $id, $participantName = null, $organizationName = null,
                                         $city = null, $issueDate = null, $history = null,
                                         $status = null, $comment = null) {
        if (is_null($participantName) && is_null($organizationName) &&
            is_null($city) && is_null($issueDate) &&
            is_null($history) && is_null($status) &&
            is_null($comment)) {
            return false;
        }

        $fields = [];
        $conn = self::connection();

        if (!is_null($participantName)) {
            $fields[] = "`participantName`='" . mysqli_real_escape_string($conn, $participantName) . "'";
        }
        if (!is_null($organizationName)) {
            $fields[] = "`organizationName`='" . mysqli_real_escape_string($conn, $organizationName) . "'";
        }
        if (!is_null($city)) {
            $fields[] = "`city`='" . mysqli_real_escape_string($conn, $city) . "'";
        }
        if (!is_null($issueDate)) {
            $fields[] = "`issueDate`='" . mysqli_real_escape_string($conn, $issueDate) . "'";
        }
        if (!is_null($history)) {
            $fields[] = "`history`='" . mysqli_real_escape_string($conn, $history) . "'";
        }
        if (!is_null($status)) {
            $fields[] = "`status`='" . mysqli_real_escape_string($conn, $status) . "'";
        }
        if (!is_null($comment)) {
            $fields[] = "`comment`='" . mysqli_real_escape_string($conn, $comment) . "'";
        }

        $setClause = implode(", ", $fields);
        $query = "UPDATE `loyal_cards` SET $setClause WHERE `id`=" . $id;

        if (mysqli_query(self::connection(), $query)) {
            return true;
        } else {
            return false;
        }
    }


    static function getPlaceCode($n): string
    {
        $n = floatval($n); // Преобразуем n в число

        if ($n <= 4.99) {
            return "D3";
        } elseif ($n <= 5.99) {
            return "D2";
        } elseif ($n <= 6.99) {
            return "D1";
        } elseif ($n <= 7.99) {
            return "L3";
        } elseif ($n <= 8.99) {
            return "L2";
        } elseif ($n <= 9.99) {
            return "L1";
        } elseif ($n === 10) {
            return "L1";
        }
        return "D3";
    }

    public static function getPlace(string $place)
    {
        return match ($place) {
            'D3' => 'Диплом III степени',
            'D2' => 'Диплом II степени',
            'D1' => 'Диплом I степени',
            'L3' => 'Лауреат III степени',
            'L2' => 'Лауреат II степени',
            'L1' => 'Лауреат I степени',
            default => 'Диплом III степени',
        };
    }



    public static function generatePlacesDoc(array $info): array
    {
        $q = "SELECT p.participantName, p.competitionProgram, p.organizationName, p.country, p.city, ROUND(AVG(s.score), 2) AS average_score FROM participants p JOIN scores s ON p.id = s.participant_id WHERE p.contestId = " . $info['contest'] . " AND p.genre = " . $info['genre'] . " AND p.nomination = " . $info['nomination'] . " AND p.category = " . $info['category'] . " AND p.age = " . $info['age'] . " AND p.level = " . $info['level'] . " GROUP BY p.id, p.participantName, p.competitionProgram, p.organizationName, p.country, p.city";
        $participants = mysqli_fetch_all(mysqli_query(self::connection(), $q), MYSQLI_ASSOC);

        usort($participants, function($a, $b) {
            return floatval($a['average_score']) <=> floatval($b['average_score']);
        });

        foreach ($participants as $participant) {
            $placeCode = self::getPlaceCode($participant['average_score']);
            $sortedParticipants[$placeCode][] = $participant;
        }

        $contestName = self::getContest((int)$info['contest'])['res'][0]['name'];

        $path = "placesDocs/";

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $pdf = new FPDF();
        $pdf->AddFont('Century Gothic','','centurygothic.php');
        $pdf->AddFont('Century Gothic','B','centurygothic_bold.php');
        $pdf->AddPage();
        $pdf->SetY(20);
        $pdf->SetFont('Century Gothic','B',14);
        $pdf->MultiCell(0, 5,iconv('utf-8', 'windows-1251', "Итоговая ведомость на " . date('d.m.Y') . " по присвоенным местам конкурса-фестиваля " . $contestName . "."), '0', 'L');
        $pdf->Ln(5);
        $pdf->MultiCell(0, 5,iconv('utf-8', 'windows-1251', "Отображаемые данные: жанр " . self::GENRES[(int)$info['genre']] . ", номинация " . self::NOMINATIONS[$info['genre']][$info['nomination']] . ", категория " . self::CATEGORIES_FULL[(int)$info['category']] . ", возрастная группа " . self::AGES[(int)$info['age']] . ", уровень подготовки " . self::LEVELS_FULL[(int)$info['level']] . "."), '0', 'L');

        $pdf->Ln(10);
        $pdf->SetFont('Century Gothic','',14);

        $n = 1;
        foreach ($participants as $participant) {
            $pdf->MultiCell(0, 6, iconv('utf-8', 'windows-1251', $n . ". " . self::getPlace(self::getPlaceCode($participant['average_score'])) . ": " . $participant['participantName'] . ", " . $participant['competitionProgram'] . ", " . $participant['organizationName'] . ", " . $participant['country'] . ", " . $participant['city'] . ";"), '0', 'L');
            $pdf->Ln(5);
            $n+=1;
        }

        $pdf->Ln(15);

        $pdf->SetFont('Century Gothic','B',14);
        $pdf->MultiCell(0, 5,iconv('utf-8', 'windows-1251', "Директор Ходько А.А. ____________        м.п."), '0', 'L');

        $pdf->Image('src/handwrite.png', 75, $pdf->GetY()-13, -300);
        $pdf->Image('src/ПЕЧАТЬ_ЦПТ.png', 120, $pdf->GetY()-15, -300);

        $pdf->Ln(10);

        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        $pdf->SetFont('Century Gothic','',14);
        $pdf->MultiCell(0, 5,iconv('utf-8', 'windows-1251', 'Общество с ограниченной ответственностью "Центр поддержки талантов"'), '0', 'L');
        $pdf->MultiCell(0, 5,iconv('utf-8', 'windows-1251', 'УНП 193644306 | +375 29 339 6106'), '0', 'L');

        $pdf->Image('src/logoZ4T.png', 10, $pdf->GetY()+5, 30);

        $name = $path.'Doc_'.date('Y-m-d').'_'.uniqid().'_'.$_COOKIE['user_id'].'.pdf';
        $pdf->Output($name, "F");
        return ["res"=>'done', "href"=>$name];
    }





}

// TODO MOVE TO 'TOOLS' CLASS
if(key_exists('action', $_GET)){
    switch ($_GET['action']){
        case 'signout':
            DB::signOut();
            echo "<script>window.location.replace('/auth')</script>";
            break;
        case 'getShortenedURLs.geN1uSme!':
            DB::printArr(DB::getShortenedURLs());
            break;
        case 'deleteUnexistedFiles.geN1uSme!':
            DB::deleteUnexistedFiles();
            break;
        case 'getTypedFilesOnContest.geN1uSme!':
            DB::printArr(DB::getTypedFilesOnContest('music', 9));
            break;
        case 'getThanks':
            $contestId = $_GET['cid'];
            $thankText = mysqli_fetch_assoc(mysqli_query(DB::connection(), "SELECT `thankText` FROM `contests` WHERE `id`= ".$contestId))['thankText'];
            $array = mysqli_fetch_all(mysqli_query(DB::connection(), "SELECT `id`, `organizationName`, `teachersList` FROM `participants` WHERE `contestId`= ".$contestId), MYSQLI_ASSOC);
            $uniqTeachersToPrint = [];
            $seenTeachers = [];

// Проход по исходному массиву
            foreach ($array as $item) {
                $organizationName = $item['organizationName'];
                $teachers = json_decode($item['teachersList'], true);

                foreach ($teachers as $teacher) {
                    $teacherName = trim($teacher['name']);
                    $teacherPosition = $teacher['position'];

                    // Проверка на уникальность по ФИО и организации
                    $key = $teacherName . '|' . $organizationName;
                    if (!isset($seenTeachers[$key])) {
                        $uniqTeachersToPrint[] = [
                            'organizationName' => $organizationName,
                            'teacherName' => $teacherName,
                            'teacherPosition' => $teacherPosition
                        ];
                        $seenTeachers[$key] = true; // Отмечаем, что педагог уже добавлен
                    }
                }
            }

// Результат
//            DB::printArr($uniqTeachersToPrint);

            $pdf = new FPDF();
            $pdf->AddFont('Century Gothic','','centurygothic.php');
            $pdf->AddFont('Century Gothic','B','centurygothic_bold.php');
            foreach ($uniqTeachersToPrint as $item) {
//                print_r($item);
                $pdf->AddPage();
                $pdf->SetY(90);
                $pdf->SetFont('Century Gothic','B',35);
                $pdf->MultiCell(0, 15,iconv('utf-8', 'windows-1251', $item['teacherName']), '0', 'C');
                if(strlen($item['teacherName'])>48){
                    $pdf->SetY(112);
                } else {
                    $pdf->SetY(98);
                }
                $pdf->SetFont('Century Gothic','',18);
                $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', $item['teacherPosition']), '0', 'C');
                if(strlen($item['teacherName'])>48){
                    $pdf->SetY(128);
                } else {
                    $pdf->SetY(110);

                }
                $pdf->SetFont('Century Gothic','B',25);
                $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', $item['organizationName']), '0', 'C');
                $pdf->MultiCell(0, 4,iconv('utf-8', 'windows-1251', ""), '0', 'C');
                $pdf->SetFont('Century Gothic','',14);
                $pdf->MultiCell(0, 7,iconv('utf-8', 'windows-1251', $thankText), '0', 'C');

            }
            $pdf->Output();
            break;

        case 'getSpecial':
            $special = DB::getSpecial($_GET['n']);
            $pdf = new FPDF();
            $pdf->AddFont('Century Gothic','','centurygothic.php');
            $pdf->AddFont('Century Gothic','B','centurygothic_bold.php');
            $pdf->AddPage();
            $pdf->SetY(80);
            $pdf->SetFont('Century Gothic','B',22);
            if(!str_contains($special['specialName'], "GRAND") && !str_contains($special['specialName'], "Гран")){
                $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', "СПЕЦИАЛЬНЫЙ ПРИЗ"), '0', 'C');
            }
            $pdf->SetFont('Century Gothic','B',30);
            $pdf->MultiCell(0, 12,iconv('utf-8', 'windows-1251', str_replace("<BR>", "\n",strtoupper($special['specialName']))), '0', 'C');
            $pdf->SetFont('Century Gothic','',20);
            $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', 'Награждается'), '0', 'C');
            $pdf->SetFont('Century Gothic','B',23);
            $pdf->MultiCell(0, 15,iconv('utf-8', 'windows-1251', $special['participantName']), '0', 'C');
            $pdf->SetFont('Century Gothic','',16);
            $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', $special['organizationName']), '0', 'C');
            $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', $special['country'].", ".$special['city']), '0', 'C');
            $pdf->SetFont('Century Gothic','B',19);
            $pdf->MultiCell(0, 12,iconv('utf-8', 'windows-1251', $special['competitionProgram']), '0', 'C');
            $pdf->SetFont('Century Gothic','',15);
            $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', str_replace("<br>", "\n", $special['teachersList'] )), '0', 'C');
            $pdf->Output();

            break;
        case 'getDiplomas':
            $contestId = $_GET['cid'];
            $array = mysqli_fetch_all(mysqli_query(DB::connection(), "SELECT `uid` FROM `participants` WHERE `contestId`= ".$contestId." ORDER BY `genre` DESC"), MYSQLI_ASSOC);
            $diplomas = array();
            $directoryGen = 'generated/';

// Проверяем, существует ли папка
            if (is_dir($directoryGen)) {
                // Получаем список файлов в папке
                $files = scandir($directoryGen);

                foreach ($files as $file) {
                    // Проверяем, что файл начинается с 'QR' и не является директорией
                    if (strpos($file, 'QR') === 0 && !is_dir($directoryGen . $file)) {
                        $filePath = $directoryGen . $file;
                        // Удаляем файл
                        if (!unlink($filePath)) {

                            echo "Ошибка при удалении файла: $filePath\n";
                        }
                    }
                }
            } else {
                echo "Указанная директория не существует: $directoryGen\n";
            }
            foreach ($array as $item) {
                $diplomas[] = DB::getParticipantsForDiplomByUId($item['uid']);
                QRcode::png('https://service.zvezdo4et.com/participant_scores?uid='.$item['uid'], 'generated/QR_'.$item['uid'].'.png');
            }
//            DB::printArr($diplomas);


            $pdf = new FPDF();
            $pdf->AddFont('Century Gothic','','centurygothic.php');
            $pdf->AddFont('Century Gothic','B','centurygothic_bold.php');
            foreach ($diplomas as $diplom) {



                $pdf->AddPage();
                if($_GET['placeForPlaces'] == "true" ){
                    $pdf->SetY(95);
                } else {
                    $pdf->SetY(80);
                }
                $pdf->SetFont('Century Gothic','',20);
                $pdf->MultiCell(0, 15,iconv('utf-8', 'windows-1251', 'Награждается'), '0', 'C');
//                if(strlen($item['teacherName'])>48){
//                    $pdf->SetY(112);
//                } else {
//                    $pdf->SetY(98);
//                }
                $pdf->SetFont('Century Gothic','B',25);
                $pdf->MultiCell(0, 15,iconv('utf-8', 'windows-1251', $diplom['participantName']), '0', 'C');
//                if(strlen($item['teacherName'])>48){
//                    $pdf->SetY(128);
//                } else {
//                    $pdf->SetY(110);
//
//                }
                $pdf->SetFont('Century Gothic','',16);
                $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', $diplom['organizationName']), '0', 'C');
                $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', $diplom['country'].", ".$diplom['city']), '0', 'C');

                $pdf->SetFont('Century Gothic','B',19);
                $pdf->MultiCell(0, 12,iconv('utf-8', 'windows-1251', $diplom['competitionProgram']), '0', 'C');

                $pdf->SetFont('Century Gothic','',14);
                $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', $diplom['genre'] . ' / ' . $diplom['nomination'] . ' / ' . $diplom['category'] . ' / ' . $diplom['age'] . ' / ' . $diplom['level']), '0', 'C');
                $pdf->MultiCell(0, 10   ,iconv('utf-8', 'windows-1251', str_replace("<br>", "\n", $diplom['teacherName'], )), '0', 'C');

                $pdf->Image('generated/QR_'.$diplom['uid'].'.png', 13, 80, 20, 20, 'PNG');
                $pdf->SetFont('Century Gothic','',8);
                $pdf->Text(10, 103, iconv('utf-8', 'windows-1251', "Личные результаты"));
//                $pdf->MultiCell(0, 10,iconv('utf-8', 'windows-1251', $item['organizationName']), '0', 'C');
//                $pdf->MultiCell(0, 4,iconv('utf-8', 'windows-1251', ""), '0', 'C');
//                $pdf->MultiCell(0, 7,iconv('utf-8', 'windows-1251', $thankText), '0', 'C');

            }
            $pdf->Output();
            break;
        case 'printDiplomas':
            $h = 0;
            if(key_exists('pids', $_GET)){
                foreach ($_GET['pids'] as $pid){
                    $h += (int)$pid;
                }
//                echo "PHP: " . md5((string)$h) . " | JS: " . $_GET['key'] . "<br>";
                if(md5("stuff".(string)$h) == $_GET['key']){



                    $array = $_GET['uids'];
                    $diplomas = array();
                    $directoryGen = 'generated/';

// Проверяем, существует ли папка
                    if (is_dir($directoryGen)) {
                        // Получаем список файлов в папке
                        $files = scandir($directoryGen);

                        foreach ($files as $file) {
                            // Проверяем, что файл начинается с 'QR' и не является директорией
                            if (strpos($file, 'QR') === 0 && !is_dir($directoryGen . $file)) {
                                $filePath = $directoryGen . $file;
                                // Удаляем файл
                                if (!unlink($filePath)) {

                                    echo "Ошибка при удалении файла: $filePath\n";
                                }
                            }
                        }
                    } else {
                        echo "Указанная директория не существует: $directoryGen\n";
                    }
                    foreach ($array as $item) {
                        $diplomas[] = DB::getParticipantsForDiplomByUId($item);
                        QRcode::png('https://service.zvezdo4et.com/participant_scores?uid='.$item, 'generated/QR_'.$item.'.png');
                    }

                    $amnt = 1;
                    $diplomaForEveryone = key_exists("diplomaForEveryone", $_GET) && $_GET['diplomaForEveryone']=="true";

                    $pdf = new FPDF();
                    $pdf->AddFont('Century Gothic','','centurygothic.php');
                    $pdf->AddFont('Century Gothic','B','centurygothic_bold.php');
                    foreach ($diplomas as $diplom) {
                        $clist = [];
                        if($diplomaForEveryone){
                            $amnt = (int)$diplom['participantsAmount'];
                        }
                        for ($i = 0; $i < $amnt; $i++) {
                            $pdf->AddPage();
                            if(key_exists("bgi", $_GET) && $_GET['bgi']=="true"){
                                $pdf->Image('src/diplomPDF.jpg', 0, 0, 210, 297, 'JPG');
                            }

                            $pdf->SetY(80);
                            $pdf->SetFont('Century Gothic', 'B', 40);
                            $pdf->MultiCell(0, 15, iconv('utf-8', 'windows-1251', strtoupper(DB::getPlace($diplom['place']))), '0', 'C');
                            $pdf->SetFont('Century Gothic', '', 20);
                            $pdf->MultiCell(0, 15, iconv('utf-8', 'windows-1251', 'Награждается'), '0', 'C');

                            $pdf->SetFont('Century Gothic', 'B', 22);
                            $pdf->MultiCell(0, 13, iconv('utf-8', 'windows-1251', $diplom['participantName']), '0', 'C');

                            if($diplomaForEveryone && count((array)$diplom['participantsList'])>1){
                                $pdf->SetFont('Century Gothic', 'B', 18);
                                $pdf->MultiCell(0, 10, iconv('utf-8', 'windows-1251', ((array)$diplom['participantsList'][$i])["name"]), '0', 'C');
                            }

                            $pdf->SetFont('Century Gothic', '', 16);
                            $pdf->MultiCell(0, 10, iconv('utf-8', 'windows-1251', $diplom['organizationName']), '0', 'C');
                            $pdf->MultiCell(0, 10, iconv('utf-8', 'windows-1251', $diplom['country'] . ", " . $diplom['city']), '0', 'C');

                            $pdf->SetFont('Century Gothic', 'B', 19);
                            $pdf->MultiCell(0, 12, iconv('utf-8', 'windows-1251', $diplom['competitionProgram']), '0', 'C');

                            $pdf->SetFont('Century Gothic', '', 14);
                            $pdf->MultiCell(0, 10, iconv('utf-8', 'windows-1251', $diplom['genre'] . ' / ' . $diplom['nomination'] . ' / ' . $diplom['category'] . ' / ' . $diplom['age'] . ' / ' . $diplom['level']), '0', 'C');
                            $pdf->MultiCell(0, 10, iconv('utf-8', 'windows-1251', str_replace("<br>", "\n", $diplom['teacherName'],)), '0', 'C');

                            $pdf->Image('generated/QR_' . $diplom['uid'] . '.png', 13, 80, 20, 20, 'PNG');
                            $pdf->SetFont('Century Gothic', '', 8);
                            $pdf->Text(10, 103, iconv('utf-8', 'windows-1251', "Личные результаты"));
                           }
                        if($diplomaForEveryone && count((array)$diplom['participantsList'])>1){
                            $pdf->AddPage();
                            if(key_exists("bgi", $_GET) && $_GET['bgi']=="true"){
                                $pdf->Image('src/diplomPDF.jpg', 0, 0, 210, 297, 'JPG');
                            }

                            $pdf->SetY(80);
                            $pdf->SetFont('Century Gothic', 'B', 40);
                            $pdf->MultiCell(0, 15, iconv('utf-8', 'windows-1251', strtoupper(DB::getPlace($diplom['place']))), '0', 'C');
                            $pdf->SetFont('Century Gothic', '', 20);
                            $pdf->MultiCell(0, 15, iconv('utf-8', 'windows-1251', 'Награждается'), '0', 'C');

                            $pdf->SetFont('Century Gothic', 'B', 22);
                            $pdf->MultiCell(0, 13, iconv('utf-8', 'windows-1251', $diplom['participantName']), '0', 'C');

                            $pdf->SetFont('Century Gothic', '', 16);
                            $pdf->MultiCell(0, 10, iconv('utf-8', 'windows-1251', $diplom['organizationName']), '0', 'C');
                            $pdf->MultiCell(0, 10, iconv('utf-8', 'windows-1251', $diplom['country'] . ", " . $diplom['city']), '0', 'C');

                            $pdf->SetFont('Century Gothic', 'B', 19);
                            $pdf->MultiCell(0, 12, iconv('utf-8', 'windows-1251', $diplom['competitionProgram']), '0', 'C');

                            $pdf->SetFont('Century Gothic', '', 14);
                            $pdf->MultiCell(0, 10, iconv('utf-8', 'windows-1251', $diplom['genre'] . ' / ' . $diplom['nomination'] . ' / ' . $diplom['category'] . ' / ' . $diplom['age'] . ' / ' . $diplom['level']), '0', 'C');
                            $pdf->MultiCell(0, 10, iconv('utf-8', 'windows-1251', str_replace("<br>", "\n", $diplom['teacherName'],)), '0', 'C');

                            $pdf->Image('generated/QR_' . $diplom['uid'] . '.png', 13, 80, 20, 20, 'PNG');
                            $pdf->SetFont('Century Gothic', '', 8);
                            $pdf->Text(10, 103, iconv('utf-8', 'windows-1251', "Личные результаты"));
                        }





                    }
                    $pidsTBD = implode(", ", $_GET['pids']);
                    if(mysqli_query(DB::connection(), "UPDATE `diplomasToPrint` SET `printed`=1 WHERE `participantId` IN (".$pidsTBD.")")){
                        $pdf->Output();
                    } else {
                        echo mysqli_error(DB::connection());
                    }






                }

            }
            break;








        case 'downloadMedia.geN1uSme!':
            if(key_exists('part', $_GET)){
                echo DB::getMedia($_GET['cid'], $_GET['kind'], $_GET['part']);
            } else {
                echo DB::getMedia($_GET['cid'], $_GET['kind']);
            }
            break;

        case 'getJurySheetForMarks':
            echo DB::getJurySheetForMarks($_GET['cid']);
            break;

//        case 'apiTest':
//            if(key_exists("token", $_GET) && $_GET['token']=="testToken123"){
//                echo json_encode(["id"=>(int)$_GET['id']+1, "return"=>"Success"]);
//            } else {
//                echo json_encode(["error"=>"Invalid token", "return"=>"Error"]);
//            }
//            break;

//        case 'ftpTest':
//            $conn_id = ftp_connect('localhost');
//            $login_result = ftp_login($conn_id, 'ftp', 'ftp');
//            ftp_pasv($conn_id, true);
//
//// Получить содержимое директории
//            $contents = ftp_rawlist($conn_id, '.');
//
//            print_r($contents);
//            if (ftp_chdir($conn_id, 'localhost/participantUploads')) {
//                $contents = ftp_rawlist($conn_id, '.');
//                DB::printArr($contents);
//            }
//
//// Закрытие соединения
//            ftp_close($conn_id);
//            echo ini_get('upload_max_filesize');
//            break;
    }
}

