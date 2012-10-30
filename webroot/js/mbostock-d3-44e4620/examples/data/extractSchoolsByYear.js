// extract each year's data from schools_data.json and store each array in separate file

var fs = require("fs");

console.log("reading schools_data.json ...");

var fs = require('fs');
var file = __dirname + '/schools_data.json';

fs.readFile(file, 'utf8', function (err, data) {
    if (err) {
        console.log('Error: ' + err);
        return;
    }

    data = JSON.parse(data);
    //console.dir(data);

    for(var year in data) {
        //console.log(data[year]);
        var str = JSON.stringify(data[year]);
        fs.writeFileSync(__dirname + "/schools_data_"+year+".json", str, 'utf8');
    }
});
