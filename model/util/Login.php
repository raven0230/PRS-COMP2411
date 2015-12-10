<?php


class Login
{
    private $conn;

    function __construct()
    {
        $this->conn = DBHelper::getConnection();
    }

    function checkCred($email, $password)
    {
        $sql = "SELECT (if((SELECT COUNT(*) FROM Account WHERE email = :email AND password = :passwordd) > 0, TRUE , FALSE ))
                AS 'matched';";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":email", $email);
            $stmt->bindValue(":password", $password);
            if ($stmt->execute()) {
                $stmt->bindColumn("matched", $matched, PDO::PARAM_BOOL);
                if ($stmt->fetch(PDO::FETCH_BOUND)) {
                    return $matched;
                } else {
                    return -1;
                }
            } else {
                return -1;
            }
        } catch (PDOException $e) {
            return -1;
        }
    }
}