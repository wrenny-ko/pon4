export class Login {
  static pageName = 'login';
  static title = 'Login';

  async setup() {
    this.resetForm();
    $('.login-submit-button')[0].addEventListener('click', Login.postLogin);
    $('.login-input-username')[0].addEventListener('keyup', this.validateInput);
    $('.login-input-password')[0].addEventListener('keyup', this.validateInput);
    $('.login-form')[0].addEventListener('keyup', this.checkEnter);
  }

  async again() {
    this.resetForm();
  }

  teardown() {
    this.resetForm();
    $('.login-submit-button')[0].removeEventListener('click', Login.postLogin);
    $('.login-input-username')[0].removeEventListener('keyup', this.validateInput);
    $('.login-input-password')[0].removeEventListener('keyup', this.validateInput);
    $('.login-form')[0].removeEventListener('keyup', this.checkEnter);
  }

  resetForm() {
    this.clearForm();
    this.disableSubmit();
  }

  clearForm() {
    $('.login-input-username')[0].value = '';
    $('.login-input-password')[0].value = '';
    $('.login-error-box')[0].innerText = '';
  }

  disableSubmit() {
    $('.login-submit-button')[0].setAttribute('disabled', '');
  }

  enableSubmit() {
    $('.login-submit-button')[0].removeAttribute('disabled');
  }

  validateInput() {
    const username = $('.login-input-username')[0].value;
    const password = $('.login-input-password')[0].value;
    if (username.length > 0 && password.length >= 5) {
      $('.login-submit-button')[0].removeAttribute('disabled');
    } else {
      $('.login-submit-button')[0].setAttribute('disabled', '');
    }
  }

  checkEnter(e) {
    if (e.keyCode === 13 && !$('.login-submit-button')[0].disabled) {
      Login.postLogin(e);
    }
  }

  static async postLogin(e) {
    e.preventDefault();

    const username = $('.login-input-username')[0].value;
    const password = $('.login-input-password')[0].value;

    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);

    await axios.post('/api/account.php?action=login', formData, {
      headers: {
        'content-type': 'multipart/form-data'
      }
    }).then( (res) => {
      window.location.href = '/user?username=' + username; // force refresh
    }).catch( (err) => {
      $('.login-error-box')[0].innerText = err.response.data.error;
    });

    return false;
  }
}
