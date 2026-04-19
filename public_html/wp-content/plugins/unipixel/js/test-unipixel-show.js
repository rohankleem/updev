// test-unipixel-show.js
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var testElement = document.getElementById('testUnipixelShowElement');
        if (testElement) {
            testElement.style.display = 'block'; // Make the element visible
            console.log('UniPixel #testUnipixelShowElement is now shown.');
        }
    }, 10000); // After 10 seconds
});
