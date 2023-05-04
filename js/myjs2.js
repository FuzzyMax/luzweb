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
   * 
   */
  class MySnippet extends HTMLElement {
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
      console.log(newElem);
      return newElem;
    }

    setData(data) {
      this.data = data;
      this.setAttribute("id", "snippet_" + data.s_id);
      console.log("setData");
      console.log(data);
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
  }
  
  customElements.define("my-book", MyBook);
  customElements.define("my-snippet", MySnippet);

  async function loadSnippets(url) {
    const response = await fetch(url);
    const jsonRes = await response.json();
    showSnippets(jsonRes);
    console.log(jsonRes);
  }

  function addSnippet(elem) {
    const sElem = document.createElement('my-snippet');
    sElem.setData(elem);
    $("#DBinhalt").append(sElem);
    console.log("Added snippet" + " with id = " + elem.s_id);
  }

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