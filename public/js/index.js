import { Search } from './search.js';
import { Scribble } from './scribble.js';
import { Draw } from './new.js';
import { Leaderboard } from './leaderboard.js';
import { User } from './user.js';

class App {
  pages = {};
  currentPageName = '';

  constructor() {
    this.pages['index'] = new Search();
    this.pages['scribble'] = new Scribble();
    this.pages['new'] = new Draw();
    this.pages['leaderboard'] = new Leaderboard();
    this.pages['user'] = new User();//TODO
    //this.pages['log'] = new Log('log', 'API logs')
    //this.pages['login'] = new Login('login', 'API logs')
    //this.pages['signup'] = new Signup('log', 'API logs')
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

/*
    const navs = document.getElementsByClassName('site-nav');
    for (const [i, nav] of Object.entries(navs)) {
      nav.addEventListener('click', (event) => {
        const newPath = event.srcElement.parentElement.id.split('-')[2];
        event.preventDefault();
        history.pushState( {} , 'Pon 4', '/' + newPath );
        App.route(this);
        return false;
      });
    }
*/
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
