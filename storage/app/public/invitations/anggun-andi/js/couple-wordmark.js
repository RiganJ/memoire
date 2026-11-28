(() => {
  const coupleNamePattern = /^Anggun\s*(?:&|dan|and)\s*Andi$/i;
  const wordmarkSelector = 'h1, h2, p, b, small, footer, span';

  const markCoupleNames = (root = document) => {
    const elements = root instanceof Element
      ? [...(root.matches(wordmarkSelector) ? [root] : []), ...root.querySelectorAll(wordmarkSelector)]
      : [...root.querySelectorAll(wordmarkSelector)];

    elements.forEach((element) => {
      if (!coupleNamePattern.test(element.textContent.trim())) {
        return;
      }

      element.classList.add('couple-signature-wordmark');
      element.setAttribute('aria-label', 'Anggun dan Andi');

      [...element.children]
        .filter((child) => !child.classList.contains('couple-signature-image'))
        .forEach((child) => child.classList.add('couple-signature-original'));

      if (!element.querySelector(':scope > .couple-signature-image')) {
        const image = document.createElement('img');
        image.className = 'couple-signature-image';
        image.src = './assets/photos/font.png';
        image.alt = '';
        image.setAttribute('aria-hidden', 'true');
        image.decoding = 'async';
        element.appendChild(image);
      }
    });
  };

  const useLamaranPhoto = (root = document) => {
    const chapters = root instanceof Element
      ? [...(root.matches('.story-chapter') ? [root] : []), ...root.querySelectorAll('.story-chapter')]
      : [...root.querySelectorAll('.story-chapter')];

    chapters.forEach((chapter) => {
      if (!chapter.querySelector('h3')?.textContent.includes('Lamaran')) {
        return;
      }

      const photo = chapter.querySelector('.story-photo img');

      if (photo) {
        photo.src = './assets/photos/lamaran.jpeg';
      }
    });
  };

  const usePhotoboothPhotos = (root = document) => {
    const strip = root instanceof Element && root.matches('.hero-strip')
      ? root
      : root.querySelector?.('.hero-strip');

    if (!strip) {
      return;
    }

    const photoSources = [
      './assets/photos/photobooth1.jpeg',
      './assets/photos/photobooth2.jpeg',
      './assets/photos/JMSS0722.jpeg',
    ];

    strip.querySelectorAll('figure img').forEach((photo, index) => {
      if (photoSources[index]) {
        photo.src = photoSources[index];
      }
    });
  };

  const optimizeAssetImages = (root = document) => {
    const images = root instanceof HTMLImageElement
      ? [root]
      : [...(root.querySelectorAll?.('img') ?? [])];

    images.forEach((image) => {
      const source = image.getAttribute('src');

      if (source?.startsWith('./assets/') && /\.(?:png|jpe?g)$/i.test(source)) {
        image.src = source.replace(/\.(?:png|jpe?g)$/i, '.webp');
      }

      image.decoding = 'async';

      if (image.closest('.asset-cover')) {
        image.loading = 'eager';
        image.fetchPriority = image.classList.contains('asset-flap') || image.closest('.asset-seal') ? 'high' : 'auto';
      } else {
        image.loading = 'lazy';
        image.fetchPriority = 'low';
      }
    });
  };

  const reduceAudioPreload = () => {
    document.querySelectorAll('audio').forEach((audio) => {
      audio.preload = 'none';

      if (audio.paused) {
        audio.load();
      }
    });
  };

  markCoupleNames();
  useLamaranPhoto();
  usePhotoboothPhotos();
  optimizeAssetImages();
  reduceAudioPreload();

  new MutationObserver((mutations) => {
    mutations.forEach((mutation) => mutation.addedNodes.forEach((node) => {
      if (node instanceof Element) {
        markCoupleNames(node);
        useLamaranPhoto(node);
        usePhotoboothPhotos(node);
        optimizeAssetImages(node);
      }
    }));
  }).observe(document.getElementById('root'), { childList: true, subtree: true });
})();
