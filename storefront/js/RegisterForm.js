class RegisterForm extends LoginForm {
    constructor() {
        super();
        this.setClass("FORM");
        this.mode = LoginForm.MODE_REGISTER;
        /**
         * Profile: an empty password keeps the current one - no challenge then
         * @type {boolean}
         */
        this.passwordOptional = false;
    }

    async onSubmit(event) {
        return super.onSubmit(event);
    }

    async processPassword() {
        const password = this.form.password.value.trim();
        const repeat = this.form.password_repeat ? this.form.password_repeat.value.trim() : password;

        if (password !== repeat) throw tr("The passwords do not match");

        if (this.passwordOptional && password.length < 1) {
            this.form.challenge.value = "";
            return;
        }
        if (password.length < 6) throw tr("At least 6 characters");

        //the trimmed values are sent - the server compares them too
        this.form.password.value = password;
        if (this.form.password_repeat) this.form.password_repeat.value = password;
        return super.processPassword();
    }

    async process() {
        return super.process();
    }

}
