function updateYoastMetaDescription() {
  var data = SpreadsheetApp.getActiveSheet().getDataRange().getValues();
  for (row in data) {
      var row = data[row];
      var options = {
          'method': 'post',
          'payload': {slug:row[1], title:row[2], desc:row[3]},
      };
      Logger.log(UrlFetchApp.fetch(row[0]+'/wp-json/wp/update-seo', options));
  }
}