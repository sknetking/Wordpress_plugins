document.addEventListener("DOMContentLoaded", function () {
    const timers = document.querySelectorAll(".countdown-timer-widget");
  
    timers.forEach((timer) => {
      const targetDate = new Date(timer.getAttribute("data-target-date")).getTime();
      const completeMessage = timer.getAttribute("data-complete-message");
      const display = timer.querySelector("#countdown-display");
      const templateContainer = document.querySelector("#countdown-template");

      if (isNaN(targetDate)) {
        display.innerHTML = "Invalid Date!";
        return;
      }
  
      const countdownInterval = setInterval(() => {
        const now = new Date().getTime();
        const timeLeft = targetDate - now;
  
        if (timeLeft <= 0) {
          display.innerHTML = completeMessage;
          if (templateContainer) {
            templateContainer.style.display = "block";
          }

          clearInterval(countdownInterval);
          return;
        }
  
        const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
  
        display.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;

    
      }, 1000);
    });
  });




  