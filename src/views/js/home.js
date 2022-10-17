// Toggle diagram fullscreen
const diagramContainer = document.querySelector('.diagram-container');

diagramContainer.addEventListener(
  'click',
  async () => await diagramContainer.requestFullscreen({ navigationUI: 'hide' })
);
