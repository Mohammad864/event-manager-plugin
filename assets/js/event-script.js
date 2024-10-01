// document.addEventListener('DOMContentLoaded', function () {
//     var eventFilterForm = document.getElementById('event-filter');
    
//     if (eventFilterForm) {
//         eventFilterForm.addEventListener('submit', function (e) {
//             e.preventDefault(); // Prevent the default form submission behavior

//             // Collect form data
//             var formData = new FormData(eventFilterForm);
//             formData.append('action', 'filter_events');
//             formData.append('security', eventManagerAjax.nonce);

//             // Prepare the request
//             var xhr = new XMLHttpRequest();
//             xhr.open('POST', eventManagerAjax.ajax_url, true);

//             // Set up a callback to handle the response
//             xhr.onreadystatechange = function () {
//                 if (xhr.readyState === 4) {
//                     if (xhr.status === 200) {
//                         var response = JSON.parse(xhr.responseText);

//                         if (response.success) {
//                             document.querySelector('.event-list').innerHTML = response.data;
//                         } else {
//                             document.querySelector('.event-list').innerHTML = '<p>Error: ' + response.data.message + '</p>';
//                         }
//                     } else {
//                         console.error('AJAX Error:', xhr.statusText);
//                         document.querySelector('.event-list').innerHTML = '<p>An error occurred while fetching events. Please try again.</p>';
//                     }
//                 }
//             };

//             // Optionally, show a loader to indicate that data is being fetched
//             document.querySelector('.event-list').innerHTML = '<p>Loading events...</p>';

//             // Send the request
//             xhr.send(formData);
//         });
//     }
// });
