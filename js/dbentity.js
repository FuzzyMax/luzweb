/**
   * @author tholuz
   * 
   */
class MyHTMLEntity extends HTMLElement {
    static parentElem = $("#DBinhalt");
    static entityName = "snippet";
    static dbTransaction = null;  //indexedDB Transaction
    static dbEntity = null;       //objectstore
    outputMode = 'default';
    data = {};
    constructor() {
        super();
    }

    /**
    * @author tholuz
    * Lädt Liste mit Daten vom Server
    * GET Aufruf der API
    */
    static async load(url) {
        const response = await fetch(url);
        const data = await response.json();
        console.log(this.entityName + " load wird aufgerufen: " + url);
        if (data.length > 0) {
          this.show(data);
        }
    }

    static show(data) {
      console.log(this.entityName + " show wird aufgerufen");
      console.log(data.length);
      this.parentElem.html('');
    
      for (let index = 0; index < data.length; index++) {
        const element = data[index];
        this.add(element);
      }
    }
  
    static add(data) {
        console.log(this.entityName + " in add");
        const newElem = document.createElement('my-' + this.entityName);
        newElem.setData(data);
        this.parentElem.append(newElem);
        if (newElem.dbEntity) {
          let dbItem = newElem.dbEntity.get(data.id);
          let dbElem = null;
          dbItem.onsuccess = function() {
            let dbElem = dbItem.result;
          };
          if (dbElem === null) {
            console.log(dbItem);
            let dbReq = newElem.dbEntity.put(data);
            dbReq.onerror = function() {
              console.log("Error DB", dbReq.error);
            };
          }
        }
    }
  
    static prepend(data) {
        let aktName = '';
        if (data.entityName) {
            aktName = 'my-' + data.entityName;
        }
        else {
            aktName = 'my-' + this.entityName;
        }
        console.log(aktName + " wird angelegt");
        let newElem = document.createElement(aktName);
        newElem.setData(data);
        this.parentElem.prepend(newElem);
    }

    static async del(id) {
        let delOK = confirm("Eintrag wirklich löschen ?");
        if (!delOK) {
          return;
        }
        url = '/localphp/del.php?auth=';
        url += localStorage['mypw'] + '//' + localStorage['myuser'];
        url += "&id=" + id + "&entityname=" + this.entityName;
        const response = await fetch(url);
        const jsonRes = await response.json();
    
        if (jsonRes.id > 0) {
          let delElem = document.getElementById(this.entityName + "_" + id);
          delElem.remove();
          console.log("Removed " + this.entityName + " with id = " + jsonRes.id);
          document.getElementById("inhalt").focus();
        }
      }
}