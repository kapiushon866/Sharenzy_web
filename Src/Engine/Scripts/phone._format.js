const phoneInput = document.getElementById('phone');

phoneInput.addEventListener('input', function (e) {
    let input = e.target.value.replace(/\D/g, '');

    if (input.length > 0) {
        input = '(' + input;
    }
    if (input.length > 3) {
        input = input.slice(0, 4) + ') ' + input.slice(4);
    }
    if (input.length > 6) {
        input = input.slice(0, 9) + '-' + input.slice(9);
    }
    if (input.length > 9) {
        input = input.slice(0, 13) + '-' + input.slice(13);
    }
    if (input.length > 14) {
        input = input.slice(0, 14);
    }

    e.target.value = input;
});
