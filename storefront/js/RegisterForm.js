class RegisterForm extends LoginForm {
    constructor() {
        super();
        this.setClass("FORM");
        this.mode = LoginForm.MODE_REGISTER;
    }

    async onSubmit(event) {
        return super.onSubmit(event);
    }
    async process() {
        return super.process();
    }

}