//alert("Start");

function chgmerk(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "merk", "id": id, "txt": txt, "auth": auth_secrete });
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function chgnina(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "link2", "id": id, "txt": txt, "auth": auth_secrete });
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function chgnotiz(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "notiz", "id": id, "txt": txt, "auth": auth_secrete });
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function chgzitat(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "zitat", "id": id, "txt": txt, "auth": auth_secrete });
  console.log(txt);
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function chgsnippet(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "snippet", "id": id, "txt": txt, "auth": auth_secrete });
  console.log(txt);
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function inputKey() {
  alert('HÄ?');
}

function checkKonf() {
  if (!localStorage['mypw'] || !localStorage['myuser']) {
    alert('Konfiguration fehlt !');
  }
}

function showSnippets(data) {
  console.log(data.length);
  $("#DBinhalt").html('');

  for (let index = 0; index < data.length; index++) {
    const element = data[index];
    addSnippet(element);
    if (index == 1) {
      alert(element.s_id);
    }
  }
}

function addSnippet(elem) {
  $("#DBinhalt").append("<strong>" + elem.s_descr + "</strong><br>");
  $("#DBinhalt").append("<textarea onchange='chgsnippet(\"" + elem.s_id + "\", this.value);'>" + elem.s_inhalt);
  $("#DBinhalt").append("</textarea><br>");
}
