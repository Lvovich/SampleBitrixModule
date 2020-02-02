(function() {
    // if admin session ends and after authorize again, this script will be loaded when window "load" event already ends
    (document.readyState !== 'complete') ? window.addEventListener('load', lie) : lie();

    function lie() {
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
    }
})();
