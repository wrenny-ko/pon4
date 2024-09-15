import { Search } from './search.js';
import { Scribble } from './scribble.js';
import { Draw } from './new.js';
import { Leaderboard } from './leaderboard.js';
import { Log } from './log.js';
import { User } from './user.js';
import { Login } from './login.js';
import { Signup } from './signup.js';
import { TicTacToe } from './tictactoe.js';

class App {
  pages = {};
  currentPageName = '';

  constructor() {
    this.pages['index'] = new Search();
    this.pages['scribble'] = new Scribble();
    this.pages['new'] = new Draw();
    this.pages['leaderboard'] = new Leaderboard();
    this.pages['user'] = new User();
    this.pages['log'] = new Log();
    this.pages['login'] = new Login();
    this.pages['signup'] = new Signup();

    if ($('#role-beta').length) {
      this.pages['tictactoe'] = new TicTacToe();
    }
  }

  run() {
    $('#search-button')[0].addEventListener('click', (event) => { 
      event.preventDefault();
      const st = $('.search-text')[0];
      const path = 'index?search=' + encodeURIComponent(st.value);
      history.pushState( {} , 'Search', '/' + path );
      App.route(this);
      return false;
    });

    document.addEventListener('click', (event) => {
      let a = event.target.closest('a');
      if(a && a.classList.contains('site-nav')) {
        event.preventDefault();
        let path = a.href.split('/').slice(3).join('/');
        history.pushState( {} , 'Pon 4', '/' + path );
        App.route(this);
        return false;
      }

      return true;
    });

    window.addEventListener('popstate', (event) => {
      App.route(this);
    });
    window.addEventListener('approute', (event) => {
      App.route(this);
    });
    App.route(this);
  }

  hidePage(name) {
    $('#' + name + '-page')[0].classList.add('hidden');
  }

  showPage(name) {
    $('#' + name + '-page')[0].classList.remove('hidden');
  }

  static route(app) {
    let urlArr = window.location.href.split('/');
    let path = urlArr[3];
    let pathArr = path.split('?');
    let pageName = pathArr[0];

    let queryString = '';
    if (pathArr.length > 1) {
      queryString = '?' + pathArr[1];
    }

    if (!(pageName in app.pages)) {
      history.replaceState( {} , 'Pon 4', '/index' + queryString );
      pageName = 'index';
    }

    if (app.currentPageName === pageName) {
      app.pages[pageName].again();
      return;
    }

    const sp = new URLSearchParams(window.location.search);
    const sch = sp.get('search');
    $('.search-text')[0].value = sch;

    if (!!app.currentPageName) {
      app.pages[app.currentPageName].teardown();
      app.hidePage(app.currentPageName);
    }

    app.pages[pageName].setup();
    app.showPage(pageName);

    app.currentPageName = pageName;
    return;
  }
}

const app = new App();
app.run();
