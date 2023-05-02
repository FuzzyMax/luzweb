function showBooks(data) {
    console.log(data.length);
    $("#DBinhalt").html('');
  
    for (let index = 0; index < data.length; index++) {
      const element = data[index];
      addBook(element);
    }
  }

  function addBook(elem) {
    console.log(elem);
    const bookElem = document.createElement('my-book');
    //bookElem.data = elem;
    bookElem.setData(elem);
    $("#DBinhalt").append(bookElem);
  }

  function prependBook(elem) {
    console.log(elem);
    const bookElem = document.createElement('my-book');
    elem.b_author = elem[0];
    elem.b_titel = elem[1];
    elem.b_isbn = elem[3];
    elem.b_bemerk = elem[4];
    bookElem.setData(elem);
    $("#DBinhalt").prepend(bookElem);
  }
  
  /**
   * @author tholuz
   * 
   */
  class MyBook extends HTMLElement {
    constructor() {
        super();
        console.log("MyBook wurde instanziert !");
    }

    setData(data) {
      this.data = data;
      this.setAttribute("id", "book_" + data.b_id);
    }
  
    connectedCallback() {
      this.innerHTML = '';
      console.log("MyBook : " + this.data.b_author);
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
  
  customElements.define("my-book", MyBook);

  function changebook(id, bemerk) {
    auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
    url = '/localphp/change.php';
    var posting = $.post(url, { "table": "buch", "id": id, "txt": bemerk, "auth": auth_secrete });
    console.log(bemerk);
    posting.done(function (data) {
      $("#ajaxErg").html(data);
    });
  }