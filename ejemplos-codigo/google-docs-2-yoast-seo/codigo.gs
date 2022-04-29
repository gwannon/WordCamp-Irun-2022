function updateYoastMetaDescription() {
  var data = SpreadsheetApp.getActiveSheet().getDataRange().getValues();
  for (row in data) {
      Logger.log(data[row]);
      var row = data[row];
      var options = {
          'method': 'put',
          'payload': { slug:row[0], desc:row[1]},
      };
      UrlFetchApp.fetch('https://pruebas.enuttisworking.com/wp-json/wp/update-seo', options);
  }
}