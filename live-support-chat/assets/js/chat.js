jQuery(document).ready(function ($) {

    const selected_user = document.querySelectorAll('input[name="selected_user"]');
    
        selected_user.forEach((radio) => {
         if(localStorage.getItem('selected_user') == radio.value){
                radio.checked = true;
                loadMessages();
            }
        });
    // Load Messages Function
    function loadMessages() {
        
        let selectedValue = "";
        selected_user.forEach((radio) => {
            if (radio.checked) {
            selectedValue = radio.value;
            }
        });

        $.post(LSC_Ajax.ajax_url, {
            action: 'load_messages',
            receiver_id:selectedValue,
        }, function (response) {
            if (response.success) {
                let messages = response.data;
                let messageHtml = '';

                messages.forEach(msg => {
                    let messageClass = msg.sender_id == LSC_Ajax.current_user_id 
                        ? 'message-self' 
                        : 'message-other';
                    let delete_btn = (LSC_Settings.delete_enabled)?`<span id='${msg.id}' class='delete_msg'>Delete</span>`:'';
                    
                    messageHtml += `<div class="${messageClass}">
                        <strong>${msg.sender_id == LSC_Ajax.current_user_id ? `You ${delete_btn}`: 'User ' + msg.sender_id}:</strong> 
                        ${msg.message}
                        </div>`;
                });

                $('#lsc-messages').html(messageHtml);
                $('#lsc-messages').scrollTop($('#lsc-messages')[0].scrollHeight); // Scroll to the bottom
            } else {
                $('#lsc-messages').html('<p>No messages yet.</p>');
            }
        });
    }

    $('input[type=radio][name=selected_user]').change(function() {
        loadMessages();
    });

    // Send Message Function
    $('#lsc-send-btn').on('click', function () {
     let message = $('#lsc-message-input').val();

        if (!message) return;

        let selectedValue = "";
        selected_user.forEach((radio) => {
            if (radio.checked) {
            selectedValue = radio.value;
            }
        });
     
        $.post(LSC_Ajax.ajax_url, {
            action: 'send_message',
            receiver_id: selectedValue,
            message: message,
        }, function (response) {
            if (response.success) {
                $('#lsc-message-input').val('');
                loadMessages();
            }
        });

         localStorage.setItem('selected_user',selectedValue);

    });

    var time_out = LSC_Settings['auto_refresh_time']*1000;
    console.log(time_out);
    // Auto-load messages every 5 seconds
    setInterval(loadMessages,time_out);
});

// delete massage 

// Add event listener to a parent element, such as 'document'
document.addEventListener('click', function (event) {
       // Check if the clicked element is the dynamically created span
    if (event.target.classList.contains('delete_msg')) {
      const spanId = event.target.id; // Get the span's ID
      //console.log('Clicked span ID:', spanId);
      jQuery.post(LSC_Ajax.ajax_url, {
        action: 'delete_message',
           message_id: spanId,
           }, function (response) {
               console.log(response);
    });
      // Perform any other actions needed
    }
    loadMessages();
  });
  


// sending image in message 
jQuery(".emojiContainer").on('.emoji','click', function () {
   
    var message_val = document.getElementById("lsc-message-input");
    message_val = jQuery(this).value;

});


function showEmojiPopup() {
    const emojiContainer = document.getElementById('emojiContainer');
    emojiContainer.innerHTML = ''; // Clear previous content
  
    // Loop through Unicode values for emojis
    for (let i = 128512; i <= 128591; i++) {
      const emoji = String.fromCodePoint(i); // Convert Unicode to emoji
  
      // Create a span element for each emoji
      const emojiSpan = document.createElement('span');
      emojiSpan.textContent = emoji;
      emojiSpan.style.margin = '5px'; // Add spacing between emojis
  
      // Add click event to append emoji to textarea
      emojiSpan.onclick = function () {
        appendEmojiToTextarea(emoji);
      };
  
      emojiContainer.appendChild(emojiSpan);
    }
      
    // Show the popup
    document.getElementById('emojiPopup').style.display = 'block';
  }
  
  function closePopup() {
    document.getElementById('emojiPopup').style.display = 'none';
  }
  
  function appendEmojiToTextarea(emoji) {
    const textarea = document.getElementById('lsc-message-input');
    textarea.value += emoji; // Append emoji to the textarea's value
  }
  