window.addEventListener('load', function() {
    var STUB = document.createElement('INPUT');

    var fileInp = document.querySelector('[name="lie-file"]') || STUB,
        fileNameInp = document.querySelector('[name="lie-filename"]') || STUB,
        errorMess = document.querySelector('.lie_error-message') || STUB;

    document.addEventListener('click', function() {
        errorMess.innerHTML = ' ';
    });

    fileInp.addEventListener('change', function() {
        fileNameInp.value = this.value.substr(this.value.lastIndexOf("\\") + 1);
    });
});
