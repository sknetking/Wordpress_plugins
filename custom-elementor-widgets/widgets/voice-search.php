<?php 
// elementor-custom-addon.php
class Custom_Voice_Search extends \Elementor\Widget_Base {

    public function get_name() {
        return 'Custom_Voice_Search';
    }

    public function get_title() {
        return __('Custom Voice Search', 'elementor-custom-addon');
    }

    public function get_icon() {
        return 'eicon-button';
    }

    public function get_categories() {
        return ['basic'];
    }

    protected function _register_controls() {
        // Button Controls
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Button Settings', 'elementor-custom-addon'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'elementor-custom-addon'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Click Me', 'elementor-custom-addon'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
		?>
		<form role="search" method="get" id="searchform" action="/">
			<input type="text" id="s" name="s" placeholder="Search..." />
			<button type="button" id="voiceSearchBtn"><img src="https://i.ibb.co/YZ6vRsz/microphone.png" class="microphone" border="0"/> </button>
			<div class='listioning-btn'>
				<span id="listeningText" style="display: none;">Listening...</span>
				<button type="button" id="stopListeningBtn" style="display: none;">Stop</button>
			</div>
			<input type="submit" id="searchsubmit" value="Search" />
						
		</form>
    <script>
       document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('s');
    const voiceSearchBtn = document.getElementById('voiceSearchBtn');
    const listeningText = document.getElementById('listeningText');
    const stopListeningBtn = document.getElementById('stopListeningBtn');
    
    if ('webkitSpeechRecognition' in window) {
        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';
        
        voiceSearchBtn.addEventListener('click', function() {
            recognition.start();
            listeningText.style.display = 'inline';
            stopListeningBtn.style.display = 'inline';
            voiceSearchBtn.style.display = 'none';
        });
        
        stopListeningBtn.addEventListener('click', function() {
            recognition.stop();
            listeningText.style.display = 'none';
            stopListeningBtn.style.display = 'none';
            voiceSearchBtn.style.display = 'inline';
        });
        
        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            searchInput.value = transcript;
            listeningText.style.display = 'none';
            stopListeningBtn.style.display = 'none';
            voiceSearchBtn.style.display = 'inline';
        };
        
        recognition.onerror = function(event) {
            console.error('Speech recognition error', event);
            listeningText.style.display = 'none';
            stopListeningBtn.style.display = 'none';
            voiceSearchBtn.style.display = 'inline';
        };
        
        recognition.onend = function() {
            listeningText.style.display = 'none';
            stopListeningBtn.style.display = 'none';
            voiceSearchBtn.style.display = 'inline';
        };
    } else {
        alert('Your browser does not support speech recognition.');
        voiceSearchBtn.disabled = true;
    }
});
    </script>
    <style>
        #listeningText {
    font-weight: bold;
    color: red;
    margin-left: 10px;
}
form#searchform {
    display: flex;
}
#stopListeningBtn {
    background-color: #ff4d4d;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    margin-left: 10px;
}
.listioning-btn {
    display: flex;
    position: absolute;
    right: 8%;
    top: 4px;
}
		#searchform button#voiceSearchBtn:hover {
        background:none!important;
}
		
.microphone{
	height:40px;
	width:40px;
}
#voiceSearchBtn {
    border: none;
    border-radius: 30px;
    position: absolute;
    right: 72px;
    top: -8px;
}
    </style>
		<?php 
	}
}