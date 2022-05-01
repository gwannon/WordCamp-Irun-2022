function updateYoastMetaDescription() {
  var data = SpreadsheetApp.getActiveSheet().getDataRange().getValues();
  for (row in data) {
    var row = data[row];
    if(row[0].match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g)) {
      var options = {
          'method': 'post',
          'payload': {slug:row[1], title:row[2], desc:row[3]},
          'headers': {token: 'TU TOKEN VA AQUÍ'},
      };
      Logger.log(UrlFetchApp.fetch(row[0]+'/wp-json/wp/update-seo', options));
    }
  }
}