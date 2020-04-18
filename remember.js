function vote(who, dir) {
  var record = JSON.parse(localStorage['ballot'] || '{}');
  if(who) {
    if(! (who in record) ) {
      record[who] = 0;
    }
    if(dir) {
      record[who] += dir;
    }
    localStorage['ballot'] = JSON.stringify(record);
    return record[who];
  }
  return record;
}

window.onload = function() {
  document.querySelectorAll(".inner").forEach( r => {
    r.addEventListener('mouseenter', e => {
      Array.from(e.target.querySelectorAll('img,source'))
        .filter(l => !l.src)
        .forEach( r => {
          r.src = r.dataset.src;
        });
      e.target.classList.add('show');
    })
    r.addEventListener('mouseleave', e => {
      Array.from(e.target.querySelectorAll('img,source'))
        .forEach( r => {
          r.removeAttribute('src');
        });
      e.target.classList.remove('show');
    })
  });
  let content = [];
  document.querySelectorAll(".cont").forEach( r => {
     let 
       user = r.dataset.user,
       controls = r.querySelector('.user');

     let count = vote(user);
     controls.innerHTML = `<a href=https://old.reddit.com/u/${user}>${user}</a>
       <b>${count}</b>
       <a onclick=up("${user}")>&#9650;</a> - 
       <a onclick=down("${user}")>&#9660;</a> 
       `;
     content.push([count, r]);
     document.body.removeChild(r);
  });
  content.sort((b,a) => a[0] - b[0]).forEach(r => {
    document.body.appendChild(r[1])
  });
}

function up(who) {
  vote(who,1);
}
function down(who) {
  vote(who,-1);
}
