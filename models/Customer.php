<?php
require_once "User.php";

class Customer extends User {

    public function commentArticle($content, $userId, $articleId) {

        $sql = "INSERT INTO comment(content,user_id,article_id)
                VALUES(?,?,?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("sii", $content, $userId, $articleId);

        return $stmt->execute();
    }

    public function createArticle($title,$summary,$content,$userId,$categoryId) {

        $sql = "INSERT INTO article(title,summary,content,user_id,category_id)
                VALUES(?,?,?,?,?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("sssii",$title,$summary,$content,$userId,$categoryId);

        return $stmt->execute();
    }

    public function editOwnArticle($articleId,$content) {

        $sql = "UPDATE article SET content=? WHERE article_id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("si",$content,$articleId);

        return $stmt->execute();
    }

    public function deleteOwnComment($commentId) {

        $sql = "DELETE FROM comment WHERE comment_id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i",$commentId);

        return $stmt->execute();
    }
}
?>