document.addEventListener("DOMContentLoaded", function() {
  const textInput = document.getElementById("text-input");
  const fontSizeRange = document.getElementById("font-size-range");
  const fontPreviews = document.querySelectorAll(".font-preview");


  function updateCards() {
    const newText = textInput.value;
    const newSize = fontSizeRange.value + "px";

    fontPreviews.forEach(preview => {
      if(newText!== ""){
      preview.textContent = newText; }
      preview.style.fontSize = newSize;
    });
 
  }
  textInput.addEventListener("input", updateCards);
  fontSizeRange.addEventListener("input", updateCards);

  updateCards();
});