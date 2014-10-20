function generatePages(currentPage, lastPage) {
    var pages = '';
    if (currentPage > 1) {
        prevPage = currentPage-1;
        pages += "<a class='block-course_managers-page-arrow' href='javascript:showPage("+prevPage+","+lastPage+");'>&lt;</a>"
    }
    if (lastPage <= 5) {
        for(var i=1;i<=lastPage;i++) {
            if (i == currentPage) {
                pages += "<span class='block-course_managers-page-numbers'>"+i+"</span>"
            } else {
                pages += "<a class='block-course_managers-page-numbers' href='javascript:showPage("+i+","+lastPage+");'>" + i + "</a>"
            }
        }
    } else if (currentPage-5 < 0) {
        for(var i=1;i<=5;i++) {
            if (i == currentPage) {
                pages += "<span class='block-course_managers-page-numbers'>"+i+"</span>"
            } else {
                pages += "<a class='block-course_managers-page-numbers' href='javascript:showPage("+i+","+lastPage+");'>" + i + "</a>"
            }
        }
        pages += "<span>&nbsp;</span>"
        pages += "<a class='block-course_managers-page-numbers' href='javascript:showPage("+lastPage+","+lastPage+");'>" + lastPage + "</a>"
    } else if (lastPage-currentPage < 5) {
        pages += "<a class='block-course_managers-page-numbers' href='javascript:showPage(1,"+lastPage+");'>1</a>"
        pages += "<span>&nbsp;</span>"
        for(var i=lastPage-4;i<=lastPage;i++) {
            if (i == currentPage) {
                pages += "<span class='block-course_managers-page-numbers'>"+i+"</span>"
            } else {
                pages += "<a class='block-course_managers-page-numbers' href='javascript:showPage("+i+","+lastPage+");'>" + i + "</a>"
            }
        }
    } else {
        pages += "<a class='block-course_managers-page-numbers' href='javascript:showPage(1,"+lastPage+");'>1</a>"
        pages += "&nbsp;"
        for(var i=currentPage-2;i<=currentPage+2;i++) {
            if (i == currentPage) {
                pages += "<span class='block-course_managers-page-numbers'>"+i+"</span>"
            } else {
                pages += "<a class='block-course_managers-page-numbers' href='javascript:showPage("+i+","+lastPage+");'>" + i + "</a>"
            }
        }
        pages += "&nbsp;"
        pages += "<a class='block-course_managers-page-numbers' href='javascript:showPage("+lastPage+","+lastPage+");'>" + lastPage + "</a>"
    }
    if (currentPage < lastPage) {
        nextPage = currentPage+1;
        pages += "<a class='block-course_managers-page-arrow' href='javascript:showPage("+nextPage+","+lastPage+");'>&gt;</a>"
    }
    document.getElementById("block-course_managers-pages").innerHTML = pages;
}

function showPage(pageNumber, lastPage) {
    if ((pageNumber >=1) && (pageNumber <= lastPage)) {
        generatePages(pageNumber, lastPage);

        for (i=1; i<=lastPage; i++) {
            elementId = "block-course_managers-page-"+i;
            document.getElementById(elementId).style.display = 'none';
        }
        
        document.getElementById("block-course_managers-page-"+pageNumber).style.display = 'block';
    }
}
