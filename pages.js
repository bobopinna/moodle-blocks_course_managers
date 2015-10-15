function generatePages(currentPage, data) {
    var itemperpage = data['itemperpage'];
    var usercount = data['elements'];
    var lastPage = Math.ceil(usercount/itemperpage);

    var pages = '';

    // Previous button
    if (currentPage > 1) {
        prevPage = currentPage-1;
        pages += "<a class='block-course_managers-page-arrow' href='javascript:showPage("+prevPage+",managers);'>&lt;</a>"
    } else {
        pages += "<span class='block-course_managers-page-arrow block-course_managers-page-current'>&lt;</span>"
    }

    // First button
    if (lastPage > 1) {
        if (currentPage > 1) {
            pages += "<a class='block-course_managers-page-number' href='javascript:showPage(1,managers);'>1</a>"
        } else {
            pages += "<span class='block-course_managers-page-number block-course_managers-page-current'>1</span>"
        }
        pages += "<span>&nbsp;</span>"
    }

    // Middle buttons
    if (lastPage >= 3) {
        if (lastPage <= 6) {
            for(var i=2;i<lastPage;i++) {
                if (i == currentPage) {
                    pages += "<span class='block-course_managers-page-number block-course_managers-page-current'>"+i+"</span>"
                } else {
                    pages += "<a class='block-course_managers-page-number' href='javascript:showPage("+i+",managers);'>" + i + "</a>"
                }
            }
        } else if (currentPage-4 < 0) {
            for(var i=2;i<=6;i++) {
                if (i == currentPage) {
                    pages += "<span class='block-course_managers-page-number block-course_managers-page-current'>"+i+"</span>"
                } else {
                    pages += "<a class='block-course_managers-page-number' href='javascript:showPage("+i+",managers);'>" + i + "</a>"
                }
            }
        } else if (lastPage-currentPage < 4) {
            for(var i=lastPage-5;i<lastPage;i++) {
                if (i == currentPage) {
                    pages += "<span class='block-course_managers-page-number block-course_managers-page-current'>"+i+"</span>"
                } else {
                    pages += "<a class='block-course_managers-page-number' href='javascript:showPage("+i+",managers);'>" + i + "</a>"
                }
            }
        } else {
            for(var i=currentPage-2;i<=currentPage+2;i++) {
                if (i == currentPage) {
                    pages += "<span class='block-course_managers-page-number block-course_managers-page-current'>"+i+"</span>"
                } else {
                    pages += "<a class='block-course_managers-page-number' href='javascript:showPage("+i+",managers);'>" + i + "</a>"
                }
            }
        }
    }

    // Last button
    if (lastPage > 1) {
        pages += "<span>&nbsp;</span>"
        if (currentPage < lastPage) {
            pages += "<a class='block-course_managers-page-number' href='javascript:showPage("+lastPage+",managers);'>"+lastPage+"</a>"
        } else {
            pages += "<span class='block-course_managers-page-number block-course_managers-page-current'>"+lastPage+"</span>"
        }
    }

    // Next button
    if (currentPage < lastPage) {
        nextPage = currentPage+1;
        pages += "<a class='block-course_managers-page-arrow' href='javascript:showPage("+nextPage+",managers);'>&gt;</a>"
    } else {
        pages += "<span class='block-course_managers-page-arrow'>&gt;</span>"
    }

    document.getElementById("block-course_managers-pages").innerHTML = pages;
}

function generateLetters(currentLetter, data) {
    var users = data['users'];
    var usercount = data['elements'];

    var letters = Array();
    for (i=1; i<=usercount; i++) {
         if (letters.indexOf(users[i]['fullname'].charAt(0).toUpperCase()) == -1) {
             letters.push(users[i]['fullname'].charAt(0).toUpperCase());
         }
    }

    
    var links = '';
    for (j=0; j<letters.length; j++) {
        if (letters[j] == currentLetter) {
            links += "<span class='block-course_managers-page-letter block-course_managers-page-current'>"+letters[j]+"</span>"
         } else {
            links += "<a class='block-course_managers-page-letter' href='javascript:showLetter(\""+letters[j]+"\",managers);'>" + letters[j] + "</a>"
         }
    }

    document.getElementById("block-course_managers-letters").innerHTML = links;
}

function showPage(pageNumber, data) {
    var itemperpage = data['itemperpage'];
    var usercount = data['elements'];
    var users = data['users'];
    var lastPage = Math.ceil(usercount/itemperpage);

    startElement = ((pageNumber-1)*itemperpage)+1;
    endElement = (pageNumber*itemperpage) < usercount ? pageNumber*itemperpage : usercount;

    if ((pageNumber >=1) && (pageNumber <= lastPage)) {
        displayedElements = '<ul class="block-course_managers-page">';
        for (i=startElement; i<=endElement; i++) {
            displayedElements +='<li><div class="link"><a href="'+users[i]['link']+'">'+users[i]['fullname']+'</a></div></li>';
        }
        displayedElements += '</ul>';
        
        document.getElementById("block-course_managers-list").innerHTML = displayedElements;
        document.getElementById("block-course_managers-list").removeAttribute('style');

        generatePages(pageNumber, data); 
    }
}

function showLetter(letter, data) {
    var itemperpage = data['itemperpage'];
    var usercount = data['elements'];
    var users = data['users'];

    if (itemperpage < usercount) {
        displayedElements = '<ul class="block-course_managers-letter">';
        for (i=1; i<=usercount; i++) {
            if (users[i]['fullname'].charAt(0).toUpperCase() == letter) {
                displayedElements +='<li><div class="link"><a href="'+users[i]['link']+'">'+users[i]['fullname']+'</a></div></li>';
            }
        }
        displayedElements += '</ul>';
        
        document.getElementById("block-course_managers-list").innerHTML = displayedElements;

        generateLetters(letter, data); 
    }
}

function showResults(filter, data) {
    var itemperpage = data['itemperpage'];
    var usercount = data['elements'];
    var users = data['users'];

    if (itemperpage < usercount) {
        displayedElements = '<ul class="block-course_managers-page">';
        for (i=1; i<=usercount; i++) {
            if (users[i]['fullname'].toLowerCase().indexOf(filter.toLowerCase()) != -1) {
                displayedElements +='<li><div class="link"><a href="'+users[i]['link']+'">'+users[i]['fullname']+'</a></div></li>';
            }
        }
        displayedElements += '</ul>';
        
        document.getElementById("block-course_managers-list").innerHTML = displayedElements;

    }
}
