function toggleNav(){
    const nav = document.getElementById('navMenu');
    if(nav){ nav.classList.toggle('show'); }
}

function confirmDelete(message = 'Yakin ingin menghapus data ini?'){
    return confirm(message);
}

function previewImage(input, targetId){
    const target = document.getElementById(targetId);
    if(input.files && input.files[0] && target){
        target.src = URL.createObjectURL(input.files[0]);
        target.style.display = 'block';
    }
}
