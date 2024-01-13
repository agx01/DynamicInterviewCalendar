function buildTable(links) {
    var links = links;    
    var linkCounter = 1;  
    var body = document.body;
    var tbl = document.createElement('table');
    tbl.style.width = '100px';
    tbl.style.border = '1px solid black';
    for(let i = 0; i<links.length / 2; i++){
    var tr = tbl.insertRow();
    for(let j = 0; j<2; j++){
    var td = tr.insertCell();
    var iframe = document.createElement('iframe');
    console.log(links[linkCounter])
                    iframe.src = links[linkCounter];
    linkCounter++;
                    iframe.style = "border-width:0";
                    iframe.width = "800px";
                    iframe.height = "600px";
    td.appendChild(iframe);
    td.style.border = '1px solid black';
    }
    }
    body.appendChild(tbl);
    }
    
    
    function convJson2Arr(data){
    var result = [];
    for(var i in data)
        result.push(data[i].apptlink);
    console.log(result);
    return result;
    }
    
    async function getCalLinks() {
        let url = 'https://script.googleusercontent.com/a/macros/jerseystem.org/echo?user_content_key=QyGafJ-Pm46qbltlVP-M4eQStGwkfOOL7EnDDkedk_54uVrLheFEpMfjTRVnb7LgJpjPJt9XopZgYj0Hr_0Ee4ZAS_BUqbzqOJmA1Yb3SEsKFZqtv3DaNYcMrmhZHmUMi80zadyHLKC-PTtxoI-OpxeBY7WrP8fk606TFosF_cnttJ-mZ4aNVRup_7fZe_mr2PtHwd0-bXZ53K26VI3_K3qyuq3s-8eyrthN1o_86hADzSoSe7-8bHd4MywCMMUyUcWaI9X1VvMYZpTak_o22Q&lib=Mu7ImnU_l2nxfe3ef5Q3v8YblZUsuwf0x';
        try {
            let res = await fetch(url);
            let data = await res.json();
            data = JSON.stringify(data);
            data = JSON.parse(data);
            data = convJson2Arr(data.data)
    //buildTable(data);
            console.log(data);
            return data;
        } catch (error) {
            console.log(error);
        }
    }
    var links = getCalLinks();
    