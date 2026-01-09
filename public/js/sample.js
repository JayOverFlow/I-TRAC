// Target the elements needed
var button = document.querySelector("#increment");
var number = document.querySelector("#number");

// Create function
function increment() {
    let currentNum = parseInt(number.textContent); // Parse the element to Int
    currentNum++; // Increment
    number.textContent = currentNum; // Update the element content
}

button.onclick = increment; // Call the functon on a button click