<?php
  class Test extends DatabaseHandler {
    public function getUser($username) {
      $sql = "SELECT * FROM players WHERE username = ?";
      //$stmt = $this->connect()->query($sql);
      $stmt = $this->connect()->prepare($sql);
      $stmt->execute([$username]);
      
      //$names = $stmt->fetch();//just one
      $rows = $stmt->fetchAll();
      
      foreach ($names as $row) {
        echo $row['email']; 
      }
      
      //return $stmt->fetch();
      
      /*
      while($row = $stmt->fetch()) {
        echo $row['email']; 
      }
      */
    }
  }
?>
