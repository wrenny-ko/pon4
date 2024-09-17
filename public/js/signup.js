export class Signup {
  static pageName = 'signup';
  static title = 'Signup';

  async setup() {
    this.resetForm();
    $('.signup-submit-button')[0].addEventListener('click', Signup.postSignup);
    $('.signup-input-username')[0].addEventListener('keyup', this.validateInput);
    $('.signup-input-email')[0].addEventListener('keyup', this.validateInput);
    $('.signup-input-password')[0].addEventListener('keyup', this.validateInput);
    $('.signup-form')[0].addEventListener('keyup', this.checkEnter);
  }

  async again() {
    this.resetForm();
  }

  teardown() {
    this.resetForm();
    $('.signup-submit-button')[0].removeEventListener('click', Signup.postSignup);
    $('.signup-input-username')[0].removeEventListener('keyup', this.validateInput);
    $('.signup-input-email')[0].addEventListener('keyup', this.validateInput);
    $('.signup-input-password')[0].removeEventListener('keyup', this.validateInput);
    $('.signup-form')[0].removeEventListener('keyup', this.checkEnter);
  }

  resetForm() {
    this.clearForm();
    this.disableSubmit();
  }

  clearForm() {
    $('.signup-input-username')[0].value = '';
    $('.signup-input-password')[0].value = '';
    $('.signup-error-box')[0].innerText = '';
  }

  disableSubmit() {
    $('.signup-submit-button')[0].setAttribute('disabled', '');
  }

  enableSubmit() {
    $('.signup-submit-button')[0].removeAttribute('disabled');
  }

  validateInput() {
    const username = $('.signup-input-username')[0].value;
    const password = $('.signup-input-password')[0].value;
    if (username.length > 0 && password.length >= 5) {
      $('.signup-submit-button')[0].removeAttribute('disabled');
    } else {
      $('.signup-submit-button')[0].setAttribute('disabled', '');
    }
  }

  checkEnter(e) {
    if (e.keyCode === 13 && !$('.signup-submit-button')[0].disabled) {
      Signup.postSignup(e);
    }
  }

  static async postSignup(e) {
    e.preventDefault();

    const username = $('.signup-input-username')[0].value;
    const email = $('.signup-input-email')[0].value;
    const password = $('.signup-input-password')[0].value;

    const formData = new FormData();
    formData.append('username', username);
    formData.append('email', email);
    formData.append('password', password);

    await axios.post('/api/account.php?action=signup', formData, {
      headers: {
        'content-type': 'multipart/form-data'
      }
    }).then( (res) => {
      window.location.pathname = '/user?username=' + username; // force refresh
    }).catch( (err) => {
      $('.signup-error-box')[0].innerText = err.response.data.error;
    });

    return false;
  }
}
