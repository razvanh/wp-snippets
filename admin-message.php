<php
public function show_message($message, $error_style=false) {
  
    if ($message) {
      if ($error_style) {
        echo '<div id="message" class="error" >';
      } else {
        echo '<div id="message" class="updated fade">';
      }
      echo $message . '</div>';
    }

  }
?>
