function handleDisplayPassword(parentDiv) {
    const inputPassword = parentDiv.querySelector('input[data-pwd]');
    const unmaskIcon = parentDiv.querySelector('.unmask > i');

    console.log(inputPassword)

    if(inputPassword.type === 'password') {
        inputPassword.type = "text"

        unmaskIcon.classList.remove('bi-eye-slash');
        unmaskIcon.classList.add('bi-eye');
    } else {
        inputPassword.type = "password"

        unmaskIcon.classList.add('bi-eye-slash');
        unmaskIcon.classList.remove('bi-eye');
    }
}

let passwordDivs = document.querySelectorAll('div:has(> input[type="password"])');
passwordDivs.forEach(passwordDiv =>  {
    const icon = document.createElement('i');
    icon.classList.add('bi', 'bi-eye-slash');

    const unmaskEl = document.createElement('span');
    unmaskEl.appendChild(icon);
    unmaskEl.classList.add('unmask');

    passwordDiv.appendChild(unmaskEl);

    unmaskEl.addEventListener('click', () => handleDisplayPassword(passwordDiv));
});