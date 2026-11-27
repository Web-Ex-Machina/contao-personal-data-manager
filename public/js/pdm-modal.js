/**
 * Simple JS Modal
 * @see : https://codepen.io/KanatSahanov/pen/raGXXa
 */
var WEM = WEM || {};
(function() {
  WEM.pdmmodal = WEM.pdmmodal || {
    selectors:{
      overlay:'jsOverlay',
      modalClose:'jsModalClose',
      modalTrigger:'jsModalTrigger',
    },
    init:function(){
      self.applyEvents(document);
    },
    applyEvents:function(){
      self.applyTriggerEvents();
    },
    applyTriggerEvents:function(){
      var modalTrigger = document.getElementsByClassName(WEM.pdmmodal.selectors.modalTrigger);
      for(var i = 0; i < modalTrigger.length; i++) {
        modalTrigger[i].onclick = function() {
          self.openModal(this.getAttribute('href').substr(1));
        }
      }

    },
    applyModalEvents:function(modalId){
      var closeButton = document.querySelector('#'+modalId).querySelectorAll('.'+WEM.pdmmodal.selectors.modalClose);
      for(var i = 0; i < closeButton.length; i++) {
        closeButton[i].onclick = function() {
          self.closeModal(modalId);
        }
      }   

      var closeOverlay = document.querySelector('#'+modalId).querySelectorAll('.'+WEM.pdmmodal.selectors.overlay);
      for(var i = 0; i < closeOverlay.length; i++) {
        closeOverlay[i].onclick = function() {
          self.closeModal(modalId);
        }
      }  
    },
    createModal:function(modalId, content){
      var modal = document.createElement('div');
      modal.innerHTML = '<div id="'+modalId+'" class="pdm-modal">' +
      '<div class="pdm-modal__overlay '+self.selectors.overlay+'"></div>' +
      '<div class="pdm-modal__container">' +
        content +
        '<button class="pdm-modal__close '+self.selectors.modalClose+'">&#10005;</button>' +
      '</div>' +
    '</div>';
      document.body.append(modal);
      self.applyModalEvents(modalId);
    },
    openModal:function(modalId){
      var modalWindow = document.getElementById(modalId);

      modalWindow.classList ? modalWindow.classList.add('open') : modalWindow.className += ' ' + 'open'; 

    },
    closeModal:function(modalId){
      var modalWindow = document.getElementById(modalId);
      modalWindow.classList ? modalWindow.classList.remove('open') : modalWindow.className = modalWindow.className.replace(new RegExp('(^|\\b)' + 'open'.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
      self.destroyModal(modalId);
    },
    destroyModal:function(modalId){
      var modal = document.getElementById(modalId);
      if(modal){
        modal.remove();
      }
    }
  };
  var self = WEM.pdmmodal;
})();
(function() {
  WEM.pdmmodal.init();
})();
