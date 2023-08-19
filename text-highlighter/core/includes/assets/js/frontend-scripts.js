/*------------------------ 
frontend related javascript
------------------------*/
jQuery(document).ready(function($) {
    /************************* some globule variables that use in all code **************************/
    var client_ip=localStorage.getItem("client_ip");

     if(localStorage.getItem("client_ip")==null)
    $.get("https://api.ipify.org/", function(data){
      client_ip = data; 
      localStorage.setItem("client_ip",data);
    });

      var postId = ajax_obj.postID;
      var ajaxUrl = ajax_obj.ajax_url;

    /************************* methods code **************************/


    $(document).on('mouseup', function() {
      var selectedText = window.getSelection().toString().trim();
  
      if (selectedText !== '') {
        // Send AJAX request to save the highlighted text
        $.ajax({
          type: 'POST',
          url: ajaxUrl,
          dataType: 'json',
          data: {
            action: 'highlighter_save_text',
            selected_text: selectedText,
            post_id: postId,
            ip_address:client_ip
          },
          success: function(response) {
          //  console.log(response[response.length].selected_text); // You can display a success message to the user here
          let data = JSON.stringify(response);
        const obj = JSON.parse(data);
        for (let x in obj) {
                  //console.log(obj[x].selected_text);
               let text = obj[x].selected_text;
                  $('#content').highlight(text,{
                    caseSensitive:true,
                  });
             }
           
          },error: function(error) {
            console.log(error); // Display an error message if the AJAX request fails
          }
        });
      }
    });

/*********************
 * highlight the selected text and  
 * after reload page get text from database and highlight it.
 * 
 ******************************************/
      $.ajax({
        type: 'POST',
        url: ajaxUrl,
        dataType: 'json',
        data: {
          action: 'highlighter_gatText',
          post_id: postId,
          ip_address:localStorage.getItem("client_ip")
        },
        success: function(response) {
       // You can display a success message to the user here
       let data = JSON.stringify(response);
        const obj = JSON.parse(data);

        for (let x in obj) {
          
               let text = obj[x].selected_text;
                  $('#content').highlight(text,{
                    className:'highlight '+ obj[x].ht_id,  
                    caseSensitive:true,
                  });
             }
          //highlightText('Quality Work');
        },error: function(error) {
          console.log(error); // Display an error message if the AJAX request fails
        }
      });

 /*********************
 * highlight the selected text and.
 * doc - https://www.jqueryscript.net/text/Fast-Word-Highlighting.html
 ******************************************/
 jQuery(document).on('mouseenter', '.highlight', function() {
    $(this).prop('title','Double click for remove selection!');
 });
    
 
   jQuery(document).on('dblclick', '.highlight', function() {
    var ht_id = $(this).attr('class').split(' ');   
    myFunction(ht_id[1]);
    $(this).removeClass('highlight');
  });
  function myFunction(ht_id) {
    // let text = confirm("Remove this selection?");
    // if (text == true) {
          $.ajax({
            type: 'POST',
            url: ajaxUrl,
            dataType: 'json',
            data: {
              action: 'remove_selection_action',
              sel_id: ht_id,
            },
            success: function(response) {
          // You can display a success message to the user here
          // let data = JSON.stringify(response);
          //   const obj = JSON.parse(data);
          //  console.log(response);

            },error: function(error) {
              console.log(error); // Display an error message if the AJAX request fails
            }
          });
       // } 
      }
  });