
// puzzle.js - simple click-to-swap tile puzzle using AniList to fetch latest anime movies
document.addEventListener('DOMContentLoaded', function(){
  const urlParams = new URLSearchParams(window.location.search);
  const levelup = urlParams.get('levelup');
  // If PHP set a JS var, prefer that (in case)
  const show = (typeof LEVEL_UP !== 'undefined' && LEVEL_UP) || levelup==='1';
  const modal = document.getElementById('animePuzzleModal');
  const closeBtn = document.getElementById('closePuzzleBtn');
  const puzzleWrap = document.getElementById('puzzleCanvasWrap');
  const puzzleMsg = document.getElementById('puzzleMsg');
  let originalOrder = [];
  let currentOrder = [];
  let cols = 3, rows = 3; // 3x3 puzzle
  let imgSrcGlobal = '';
  closeBtn.addEventListener('click', ()=>{ modal.style.display='none'; });

  if (show) {
    openModalAndStart();
  }

  function openModalAndStart(){
    modal.style.display='flex';
    puzzleWrap.innerHTML = '<div>Loading puzzle...</div>';
    fetchLatestAnimeMovieCover().then(src=>{
      imgSrcGlobal = src;
      return createPuzzleFromImage(src);
    }).catch(err=>{
      puzzleWrap.innerHTML = '<div style="color:red">Failed to load image for puzzle.</div>';
      console.error(err);
    });
  }

  async function fetchLatestAnimeMovieCover(){
    // Use AniList GraphQL to get latest anime movies
    const q = `
    query ($perPage:Int){
      Page(perPage:$perPage){
        media(type: ANIME, format: MOVIE, sort:START_DATE_DESC){
          id title { romaji } coverImage { large }
        }
      }
    }`;
    const variables = { perPage: 20 };
    const res = await fetch('https://graphql.anilist.co', {
      method:'POST',
      headers:{'Content-Type':'application/json','Accept':'application/json'},
      body: JSON.stringify({ query: q, variables })
    });
    const j = await res.json();
    if (!j.data || !j.data.Page || !j.data.Page.media || j.data.Page.media.length===0) {
      throw new Error('No media found from AniList');
    }
    // pick random from top results
    const picks = j.data.Page.media;
    const pick = picks[Math.floor(Math.random()*picks.length)];
    return pick.coverImage.large;
  }

  async function createPuzzleFromImage(src){
    puzzleWrap.innerHTML = '';
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.src = src;
    await new Promise((res,rej)=>{ img.onload=res; img.onerror=rej; });
    // scale image to fit container
    const maxW = Math.min(600, img.width);
    const scale = maxW / img.width;
    const canvasW = Math.round(img.width * scale);
    const canvasH = Math.round(img.height * scale);
    // create hidden canvas to extract tiles
    const baseCanvas = document.createElement('canvas');
    baseCanvas.width = canvasW; baseCanvas.height = canvasH;
    const bctx = baseCanvas.getContext('2d');
    bctx.drawImage(img, 0, 0, canvasW, canvasH);

    const tileW = Math.floor(canvasW / cols);
    const tileH = Math.floor(canvasH / rows);

    // create tiles array
    originalOrder = [];
    for (let r=0;r<rows;r++){
      for (let c=0;c<cols;c++){
        const x=c*tileW, y=r*tileH;
        const tileCanvas = document.createElement('canvas');
        tileCanvas.width = tileW; tileCanvas.height = tileH;
        const tctx = tileCanvas.getContext('2d');
        tctx.drawImage(baseCanvas, x, y, tileW, tileH, 0,0,tileW,tileH);
        originalOrder.push(tileCanvas.toDataURL());
      }
    }
    // shuffle to currentOrder
    currentOrder = shuffleArray(originalOrder.slice());
    renderTiles();
  }

  function renderTiles(){
    puzzleWrap.innerHTML = '';
    const grid = document.createElement('div');
    grid.style.display='grid';
    grid.style.gridTemplateColumns = `repeat(${cols}, auto)`;
    grid.style.gap = '4px';
    puzzleWrap.appendChild(grid);

    // create tiles as buttons
    currentOrder.forEach((dataUrl, idx)=>{
      const btn = document.createElement('button');
      btn.style.padding='0';
      btn.style.border='1px solid #ccc';
      btn.style.background='transparent';
      btn.style.width = 'auto';
      const img = document.createElement('img');
      img.src = dataUrl;
      img.width = 120;
      img.height = 120;
      btn.appendChild(img);
      btn.dataset.idx = idx;
      btn.onclick = onTileClick;
      grid.appendChild(btn);
    });
    puzzleMsg.textContent = 'Click two tiles to swap. Solve the puzzle to earn the image (it will auto-download).';
  }

  let firstSelection = null;
  function onTileClick(e){
    const idx = parseInt(e.currentTarget.dataset.idx);
    if (firstSelection === null){
      firstSelection = idx;
      e.currentTarget.style.outline = '3px solid #4CAF50';
      return;
    } else {
      // swap tiles
      const second = idx;
      // swap in currentOrder
      const tmp = currentOrder[firstSelection];
      currentOrder[firstSelection] = currentOrder[second];
      currentOrder[second] = tmp;
      firstSelection = null;
      renderTiles();
      checkSolved();
    }
  }

  function checkSolved(){
    // compare currentOrder to originalOrder
    let ok = true;
    for (let i=0;i<originalOrder.length;i++){
      if (currentOrder[i] !== originalOrder[i]) { ok = false; break; }
    }
    if (ok){
      puzzleMsg.textContent = 'Solved! Preparing your reward...';
      // auto-download original full image (not tile)
      downloadImageAsBlob(imgSrcGlobal, 'anime_reward.jpg').then(()=>{
        puzzleMsg.textContent = 'Downloaded — check your downloads folder. Closing soon...';
        setTimeout(()=>{ modal.style.display='none'; location.href = window.location.pathname; }, 2000);
      }).catch(err=>{
        puzzleMsg.textContent = 'Solved! but failed to download automatically. Right-click image to save.';
        console.error(err);
      });
    }
  }

  function shuffleArray(arr){
    for (let i=arr.length-1;i>0;i--){
      const j = Math.floor(Math.random()*(i+1));
      [arr[i],arr[j]] = [arr[j],arr[i]];
    }
    return arr;
  }

  async function downloadImageAsBlob(src, filename){
    const resp = await fetch(src, {mode:'cors'});
    if (!resp.ok) throw new Error('Failed to fetch image for download');
    const blob = await resp.blob();
    const a = document.createElement('a');
    const url = URL.createObjectURL(blob);
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

});
