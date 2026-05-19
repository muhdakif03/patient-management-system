// Function to toggle time inputs in Doctor Schedule
function toggleTimes(day) {
    var checkbox = document.getElementById('switch_' + day);
    var timeDiv = document.getElementById('times_' + day);
    if(checkbox && timeDiv) {
        if(checkbox.checked) {
            timeDiv.style.display = 'flex';
        } else {
            timeDiv.style.display = 'none';
        }
    }
}

// Function to handle Doctor Selection in Booking Page
function showSchedule() {
    var select = document.getElementById('doctorSelect');
    var display = document.getElementById('scheduleDisplay');
    var list = document.getElementById('scheduleList');
    
    if(!select || !display || !list) return;

    var selectedOption = select.options[select.selectedIndex];
    var scheduleJson = selectedOption.getAttribute('data-schedule');

    list.innerHTML = ''; 

    if (scheduleJson) {
        try {
            var schedule = JSON.parse(scheduleJson);
            display.classList.remove('d-none');
            
            for (var day in schedule) {
                if (schedule[day] !== 'Closed') {
                    var li = document.createElement('li');
                    li.innerHTML = `<strong>${day}:</strong> ${schedule[day]}`;
                    list.appendChild(li);
                }
            }
        } catch (e) {
            console.error("Error parsing schedule JSON");
            display.classList.add('d-none');
        }
    } else {
        display.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Select all elements with the 'alert' class (Bootstrap alerts)
    const alerts = document.querySelectorAll('.alert');

    if (alerts.length > 0) {
        alerts.forEach(function(alert) {
            // Wait 3 seconds (3000 milliseconds)
            setTimeout(function() {
                // 1. Start Fade Out Transition
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";

                // 2. Remove from DOM after transition (0.5s) is done
                setTimeout(function() {
                    alert.remove();
                }, 500); 
            }, 3000);
        });
    }
});