<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<?php if (is_user_logged_in()) : ?>
<div class="lsc-container row g-2">
    <div class="lsc-user-list col-sm-12 col-md-3">
        <h4 class='heading'>Select A User</h4>
        <ul id="lsc-users">
            <?php
            $users = get_users(array('fields' => array('ID', 'user_nicename')));
         if(get_current_user_id()== 1 || get_option('lsc_user_chat')==true):
    
            foreach ($users as $user) {

                if(get_current_user_id() == $user->ID){
                    continue;
                }
                // Display the user ID
                echo '<li> <label><input type="radio" name="selected_user" class="chat_with" value="'.$user->ID.'">';
                // Display the user nicename (username slug)
                echo $user->user_nicename . '</label> </li>';
            }
        else: 
        echo '<li> <label><input type="radio" name="selected_user" class="chat_with" value="1">Chat With Admin </label> </li>';

        endif;
            ?>

        </ul>
        
        </div>

    <div class="lsc-chat-window col-sm-12 col-md-9">
        <div id="lsc-messages"></div>
        <div class='send_message_tools'>
        <textarea id="lsc-message-input" placeholder="Type a message..."> </textarea>   
        <span class='send_emoji' onclick="showEmojiPopup()">😀</span>
        <button id="lsc-send-btn">Send</button>
        </div>
    </div>
</div>
<?php else : ?>
<p>Please log in to use the chat. <a href="<?php echo site_url().'/wp-admin';?>">Login</a></p>
<?php endif; ?>

<!-- popup emoji div  -->
<div id="emojiPopup">
  <button onclick="closePopup()" class='close'>Close</button>
  
  <span id="emojiContainer" style="font-size:48px;"></span>
</div>
 <!--end popup emoji div  -->
