function showBooks(data) {
    console.log(data.length);
    $("#DBinhalt").html('');
  
    for (let index = 0; index < data.length; index++) {
      const element = data[index];
      addBook(element);
    }
  }

  function addBook(elem) {
    const bookElem = document.createElement('my-book');
    bookElem.setData(elem);
    $("#DBinhalt").append(bookElem);
  }

  function prepend_book(elem) {
    console.log("prepend book");
    console.log(elem);
    const bookElem = document.createElement('my-book');
    elem.b_author = elem[0];
    elem.b_titel = elem[1];
    elem.b_isbn = elem[3];
    elem.b_bemerk = elem[4];
    bookElem.setData(elem);
    $("#DBinhalt").prepend(bookElem);
  }

  async function loadBooks(url) {
    const response = await fetch(url);
    const jsonRes = await response.json();
    showBooks(jsonRes);
  }

  function changebook(id, bemerk) {
    auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
    url = '/localphp/change.php';
    var posting = $.post(url, { "table": "buch", "id": id, "txt": bemerk, "auth": auth_secrete });
    console.log(bemerk);
    posting.done(function (data) {
      $("#ajaxErg").html(data);
    });
  }
  
  /**
   * @author tholuz
   * 
   */
  class MyBook extends HTMLElement {
    constructor() {
        super();
        //console.log("MyBook wurde instanziert !");
    }

    setData(data) {
      this.data = data;
      this.setAttribute("id", "book_" + data.b_id);
    }
  
    connectedCallback() {
      this.innerHTML = '';
      //console.log("MyBook : " + this.data.b_author);
      var h3elem = document.createElement('h3');
      h3elem.textContent = this.data.b_author;
      this.appendChild(h3elem);
      var pelem = document.createElement('p');
      pelem.textContent = this.data.b_titel;
      this.appendChild(pelem);
      var pelem2 = document.createElement('p');
      pelem2.textContent = this.data.b_isbn;
      this.appendChild(pelem2);
      var textelem = document.createElement('textarea');
      textelem.setAttribute("onchange","changebook("+ this.data.b_id + ",this.value" +");");
      textelem.textContent = this.data.b_bemerk;
      this.appendChild(textelem);
      var phr = document.createElement('hr');
      this.appendChild(phr);
    }
  }

  /**
   * @author tholuz
   * web component zur Darstellung der DBTabelle snippet
   */
  window["MySnippet"] = class MySnippet extends HTMLElement {
    static parentElem = $("#DBinhalt");
    static entityName = "snippet";
    constructor() {
        super();
    }

    /**
     * Erzeugt ein neues HTML-Element in mySnippet
     * @param {*} name 
     * @param {*} text 
     * @param {*} clickfunc 
     * @returns  
     */
    addElem(name, text, clickfunc) {
      var newElem = document.createElement(name);
      newElem.textContent = text;
      if (clickfunc) {
        newElem.setAttribute("onclick", clickfunc);
      }
      return newElem;
    }

    setData(data) {
      this.data = data;
      this.setAttribute("id", "snippet_" + data.s_id);
    }
  
    connectedCallback() {
      this.innerHTML = '';
      var divElem = document.createElement('div');
      var h3elem = document.createElement('h3');
      h3elem.textContent = this.data.s_descr;
      divElem.appendChild(h3elem);
      var pelem = document.createElement('p');
      pelem.textContent = this.data.s_date;
      pelem.appendChild(this.addElem("div"," DEL ", "delEntity("+ this.data.s_id +",'snippet');"));
      divElem.appendChild(pelem);
      var textelem = document.createElement('textarea');
      textelem.setAttribute("onchange","chgsnippet("+ this.data.s_id + ",this.value" +");");
      textelem.textContent = this.data.s_inhalt;
      divElem.appendChild(textelem);
      this.appendChild(divElem);
    }

    static async load(url) {
      const response = await fetch(url);
      const data = await response.json();
      console.log(data.length);
      $("#DBinhalt").html('');
    
      for (let index = 0; index < data.length; index++) {
        const element = data[index];
        MySnippet.add(element);
      }
    }

    static add(data) {
      const nElem = document.createElement('my-snippet');
      nElem.setData(data);
      MySnippet.parentElem.append(nElem);
    }

    static prepend(data) {
      console.log("prepend snippet");
      console.log(data);
      const nElem = document.createElement('my-snippet');
      nElem.setData(data);
      MySnippet.parentElem.prepend(nElem);
    }
  }

  /**
   * @author tholuz
   * web component zur Darstellung der DBTabelle zitat
   */
  window["MyZitat"] = class MyZitat extends HTMLElement {
    static parentElem = $("#DBinhalt");
    constructor() {
        super();
        this.toggleStr = "";
    }

    /**
     * Erzeugt ein neues HTML-Element in myZitat
     * @param {*} name 
     * @param {*} text 
     * @param {*} clickfunc 
     * @returns  
     */
    addElem(name, text, clickfunc) {
      var newElem = document.createElement(name);
      newElem.textContent = text;
      if (clickfunc) {
        newElem.setAttribute("onclick", clickfunc);
      }
      return newElem;
    }

    setData(data) {
      this.data = data;
      this.setAttribute("id", "zitat_" + data.z_id);
    }
  
    connectedCallback() {
      this.innerHTML = '';
      var divElem = document.createElement('div');

      var helem = document.createElement('h5');
      helem.textContent = this.data.z_quelle;
      divElem.appendChild(helem);
      
      var pelem = document.createElement('p');
      pelem.textContent = this.data.b_author;
      pelem.appendChild(this.addElem("div"," DEL ", "delEntity("+ this.data.z_id +",'zitat');"));
      divElem.appendChild(pelem);
      
      var spanelem = document.createElement('span');
      spanelem.setAttribute("onclick","$('#z" + this.data.z_id + "').toggle();");
      spanelem.textContent = 'Details';
      divElem.appendChild(spanelem);
      
      var div2Elem = document.createElement('div');
      div2Elem.setAttribute("class","detail");
      div2Elem.setAttribute("id","z"+ this.data.z_id);
      div2Elem.setAttribute("style","display:none");
      var textelem = document.createElement('textarea');
      textelem.setAttribute("onchange","chgzitat("+ this.data.z_id + ",this.value" +");");
      textelem.textContent = this.data.z_inhalt;
      div2Elem.appendChild(textelem);
      divElem.appendChild(div2Elem);

      this.appendChild(divElem);
    }

    static async load(url) {
      const response = await fetch(url);
      const data = await response.json();
      console.log(data.length + " Zitate geladen");
      $("#DBinhalt").html('');
    
      for (let index = 0; index < data.length; index++) {
        const element = data[index];
        this.add(element);
      }
    }

    static add(data) {
      const zElem = document.createElement('my-zitat');
      zElem.setData(data);
      MyZitat.parentElem.append(zElem);
    }

    static prepend(data) {
      console.log("prepend_zitat");
      console.log(data);
      const zElem = document.createElement('my-zitat');
      zElem.setData(data);
      MyZitat.parentElem.prepend(zElem);
    }
  }

  /**
   * @author tholuz
   * web component zur Datstellung der DBTabelle chat
  */
  window["MyChat"] = class MyChat extends HTMLElement {
    static parentElem = $("#DBinhalt");
    static entityName = "chat";
    constructor() {
        super();
    }

    /**
     * Erzeugt ein neues HTML-Element in myChat
     * @param {*} name 
     * @param {*} text 
     * @param {*} clickfunc 
     * @returns  
     */
    addElem(name, text, clickfunc) {
      var newElem = document.createElement(name);
      newElem.textContent = text;
      if (clickfunc) {
        newElem.setAttribute("onclick", clickfunc);
      }
      return newElem;
    }

    setData(data) {
      this.data = data;
      this.setAttribute("id", "chat_" + data.id);
    }
  
    connectedCallback() {
      this.innerHTML = '';
      var div1 = document.createElement('div');
      var div2 = document.createElement('div');
      div1.textContent = this.data.creator_nic;
      div1.setAttribute("class", "chatfrom");
      div2.textContent = this.data.content;
      div2.setAttribute("class", "chat");
      this.appendChild(div1);
      this.appendChild(div2);
    }

    static async load(url) {
      const response = await fetch(url);
      const data = await response.json();
      console.log(data.length + "chat");
      $("#DBinhalt").html('');
    
      for (let index = 0; index < data.length; index++) {
        const element = data[index];
        MyChat.add(element);
      }
    }

    static add(data) {
      const chatElem = document.createElement('my-chat');
      chatElem.setData(data);
      MyChat.parentElem.append(chatElem);
    }

    static prepend(elem) {
      console.log("prepend_chat");
      const sElem = document.createElement('my-chat');
      sElem.setData(elem);
      MyChat.parentElem.prepend(sElem);
    }

  }

  /**
   * @author tholuz
   * web component zur Darstellung der DBTabelle Notiz
  */
  window["MyNotiz"] = class MyNotiz extends HTMLElement {
    static parentElem = $("#DBinhalt");
    static entityName = "notiz";
    constructor() {
        super();
    }

    /**
     * Erzeugt ein neues HTML-Element in myZitat
     * @param {*} name 
     * @param {*} text 
     * @param {*} clickfunc 
     * @returns  
     */
    addElem(name, text, clickfunc) {
      var newElem = document.createElement(name);
      newElem.textContent = text;
      if (clickfunc) {
        newElem.setAttribute("onclick", clickfunc);
      }
      return newElem;
    }

    setData(data) {
      this.data = data;
      this.setAttribute("id", "notiz_" + data.notiz_id);
    }
  
    connectedCallback() {
      this.innerHTML = '';
      var divElem = document.createElement('div');
      var h3elem = document.createElement('h3');
      h3elem.textContent = this.data.notiz_kopf;
      divElem.appendChild(h3elem);
      var pelem = document.createElement('p');
      pelem.textContent = this.data.created;
      pelem.appendChild(this.addElem("div"," DEL ", "delEntity("+ this.data.notiz_id +",'notiz');"));
      divElem.appendChild(pelem);
      var textelem = document.createElement('textarea');
      textelem.setAttribute("onchange","chgnotiz("+ this.data.notiz_id + ",this.value" +");");
      textelem.textContent = this.data.notiz_inhalt;
      divElem.appendChild(textelem);
      this.appendChild(divElem);
    }

    static async load(url) {
      const response = await fetch(url);
      const data = await response.json();
      console.log(data.length);
      $("#DBinhalt").html('');
    
      for (let index = 0; index < data.length; index++) {
        const element = data[index];
        MyNotiz.add(element);
      }
    }

    static add(data) {
      const nElem = document.createElement('my-notiz');
      nElem.setData(data);
      MyNotiz.parentElem.append(nElem);
    }

    static prepend(data) {
      console.log("prepend notiz");
      console.log(data);
      const nElem = document.createElement('my-notiz');
      nElem.setData(data);
      MyNotiz.parentElem.prepend(nElem);
    }

  }

  /**
   * @author tholuz
   * web component zur Darstellung der DBTabelle merkzettel
  */
  window["MyLink"] = class MyLink extends HTMLElement {
    static parentElem = $("#DBinhalt");
    static entityName = "link";
    constructor() {
        super();
    }

    /**
     * Erzeugt ein neues HTML-Element in myLink
     * @param {*} name 
     * @param {*} text 
     * @param {*} clickfunc 
     * @returns  
     */
    addElem(name, text, clickfunc) {
      var newElem = document.createElement(name);
      newElem.textContent = text;
      if (clickfunc) {
        newElem.setAttribute("onclick", clickfunc);
      }
      return newElem;
    }

    setData(data) {
      this.data = data;
      this.setAttribute("id", "link_" + data.mz_id);
    }
  
    connectedCallback() {
      this.innerHTML = '';
      var divElem = document.createElement('div');
      if (this.data.mz_txt.slice(0,8) == 'https://'
          || this.data.mz_txt.slice(0,7) == 'http://') {
            var linkelem = document.createElement('a');
            linkelem.setAttribute("href",this.data.mz_txt);
            linkelem.textContent = this.data.mz_short;
            divElem.appendChild(linkelem);
            var elem = document.createElement('br');
            divElem.append(elem);
      }
      else {
        var helem = document.createElement('h4');
        helem.textContent = this.data.mz_short;
        divElem.appendChild(helem);
        var elem = document.createElement('p');
        elem.textContent = this.data.mz_txt;
        divElem.appendChild(elem);
      }
      var pelem = document.createElement('span');
      pelem.textContent = this.data.mz_date;
      pelem.appendChild(this.addElem("span"," DEL ", "delEntity("+ this.data.mz_id +",'link');"));
      divElem.appendChild(pelem);
      this.appendChild(divElem);
    }

    static async load(url) {
      const response = await fetch(url);
      const data = await response.json();
      console.log(data.length);
      $("#DBinhalt").html('');
    
      for (let index = 0; index < data.length; index++) {
        const element = data[index];
        MyLink.add(element);
      }
    }

    static add(data) {
      const nElem = document.createElement('my-link');
      nElem.setData(data);
      MyLink.parentElem.append(nElem);
    }

    static prepend(data) {
      console.log("prepend link");
      console.log(data);
      const nElem = document.createElement('my-link');
      nElem.setData(data);
      MyLink.parentElem.prepend(nElem);
    }

  }

  /**
   * @author tholuz
   * web component zur Darstellung der DBTabelle nina
  */
  window["MyLink2"] = class MyLink2 extends HTMLElement {
    static parentElem = $("#DBinhalt");
    constructor() {
        super();
    }

    /**
     * Erzeugt ein neues HTML-Element in myLink2
     * @param {*} name 
     * @param {*} text 
     * @param {*} clickfunc 
     * @returns  
     */
    addElem(name, text, clickfunc) {
      var newElem = document.createElement(name);
      newElem.textContent = text;
      if (clickfunc) {
        newElem.setAttribute("onclick", clickfunc);
      }
      return newElem;
    }

    setData(data) {
      this.data = data;
      this.setAttribute("id", "link2_" + data.n_id);
    }
  
    connectedCallback() {
      this.innerHTML = '';
      var divElem = document.createElement('div');
      if (this.data.n_txt.slice(0,8) == 'https://'
          || this.data.n_txt.slice(0,7) == 'http://') {
            var linkelem = document.createElement('a');
            linkelem.setAttribute("href",this.data.n_txt);
            linkelem.textContent = this.data.n_short;
            divElem.appendChild(linkelem);
            var elem = document.createElement('br');
            divElem.append(elem);
      }
      else {
        var helem = document.createElement('h4');
        helem.textContent = this.data.n_short;
        divElem.appendChild(helem);
        var elem = document.createElement('p');
        elem.textContent = this.data.n_txt;
        divElem.appendChild(elem);
      }
      var pelem = document.createElement('span');
      pelem.textContent = this.data.n_date;
      pelem.appendChild(this.addElem("span"," DEL ", "delEntity("+ this.data.n_id +",'link2');"));
      divElem.appendChild(pelem);
      this.appendChild(divElem);
    }

    static async load(url) {
      const response = await fetch(url);
      const data = await response.json();
      console.log(data.length);
      $("#DBinhalt").html('');
    
      for (let index = 0; index < data.length; index++) {
        const element = data[index];
        MyLink2.add(element);
      }
    }

    static add(data) {
      const nElem = document.createElement('my-link2');
      nElem.setData(data);
      MyLink2.parentElem.append(nElem);
    }

    static prepend(data) {
      console.log("prepend link2");
      console.log(data);
      const nElem = document.createElement('my-link2');
      nElem.setData(data);
      MyLink2.parentElem.prepend(nElem);
    }

  }
  
  customElements.define("my-book", MyBook);
  customElements.define("my-snippet", MySnippet);
  customElements.define("my-zitat", MyZitat);
  customElements.define("my-chat", MyChat);
  customElements.define("my-notiz", MyNotiz);
  customElements.define("my-link", MyLink);
  customElements.define("my-link2", MyLink2);


  function prepend_snippet(elem) {
    console.log("prepend_snippet");
    console.log(elem);
    const sElem = document.createElement('my-snippet');
    elem.s_descr = elem[0];
    elem.s_inhalt = elem[1];
    elem.s_date = "NEU";
    sElem.setData(elem);
    $("#DBinhalt").prepend(sElem);
  }

  function prepend_chat(elem) {
    MyChat.prepend(elem);
  }

  async function loadChat(url) {
    const response = await fetch(url);
    const jsonRes = await response.json();
    showChat(jsonRes);
  }

  function showChat(data) {
    console.log(data.length);
    $("#DBinhalt").html('');
  
    for (let index = 0; index < data.length; index++) {
      const element = data[index];
      MyChat.add(element);
    }
  }

  function prepend_notiz(elem) {
    MyNotiz.prepend(elem);
  }

  function prepend_link(elem) {
    MyLink.prepend(elem);
  }

  function prepend_link2(elem) {
    MyLink2.prepend(elem);
  }

  async function loadNotiz(url) {
    const response = await fetch(url);
    const data = await response.json();
    console.log(data.length);
    $("#DBinhalt").html('');
  
    for (let index = 0; index < data.length; index++) {
      const element = data[index];
      MyNotiz.add(element);
    }
    //showNotiz(jsonRes);
  }

  function prepend_zitat(elem) {
    MyZitat.prepend(elem);
  }

  async function loadZitat(url) {
    const response = await fetch(url);
    const data = await response.json();
    console.log(data.length);
    $("#DBinhalt").html('');
  
    for (let index = 0; index < data.length; index++) {
      const element = data[index];
      MyZitat.add(element);
    }
  }

  async function delEntity(id, entityName) {
    url = '/localphp/del.php?auth=';
    url += localStorage['mypw'] + '//' + localStorage['myuser'];
    url += "&id=" + id + "&entityname=" + entityName;
    const response = await fetch(url);
    const jsonRes = await response.json();

    if (jsonRes.id > 0) {
      delElem = document.getElementById(entityName + "_" + id);
      delElem.remove();
      console.log("Removed " + entityName + " with id = " + jsonRes.id);
    }
  }

  async function addEntity(entityName, $data) {
    url = '/localphp/save.php?auth=';
    url += localStorage['mypw'] + '//' + localStorage['myuser'];
    url += "&id=" + id + "&table=" + entityName;

    const response = await fetch(url);
    const jsonRes = await response.json();

  }