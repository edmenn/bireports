const STORAGE_KEYS = {
  sidebar: 'app.sidebar.state',
  fullscreen: 'app.fullscreen.state',
};

const FULLSCREEN_EVENT_NAMES = [
  'fullscreenchange',
  'webkitfullscreenchange',
  'mozfullscreenchange',
  'MSFullscreenChange',
];

const FULLSCREEN_GESTURE_EVENTS = ['pointerdown', 'keydown', 'touchstart'];

const getStoredValue = (key) => {
  try {
    return window.localStorage.getItem(key);
  } catch (error) {
    return null;
  }
};

const setStoredValue = (key, value) => {
  try {
    window.localStorage.setItem(key, value);
  } catch (error) {
    /* ignore quota/storage issues */
  }
};

const removeStoredValue = (key) => {
  try {
    window.localStorage.removeItem(key);
  } catch (error) {
    /* ignore */
  }
};

const isFullscreenActive = () => Boolean(
  document.fullscreenElement ||
  document.webkitFullscreenElement ||
  document.mozFullScreenElement ||
  document.msFullscreenElement,
);

let lastSidebarState = null;

const persistSidebarState = () => {
  const collapsed = document.body.classList.contains('sidebar-collapse');
  const nextState = collapsed ? 'collapsed' : 'expanded';

  if (lastSidebarState === nextState) {
    return;
  }

  lastSidebarState = nextState;
  setStoredValue(STORAGE_KEYS.sidebar, nextState);
};

const applySidebarStateFromStorage = () => {
  const stored = getStoredValue(STORAGE_KEYS.sidebar);

  if (stored === 'collapsed') {
    document.body.classList.add('sidebar-collapse');
    document.body.classList.remove('sidebar-open');
    lastSidebarState = 'collapsed';
  } else if (stored === 'expanded') {
    document.body.classList.remove('sidebar-collapse');
    lastSidebarState = 'expanded';
  } else {
    lastSidebarState = document.body.classList.contains('sidebar-collapse') ? 'collapsed' : 'expanded';
  }
};

const setupSidebarPersistence = () => {
  applySidebarStateFromStorage();

  const toggleSelectors = ['[data-lte-toggle="sidebar"]', '[data-widget="pushmenu"]'];
  toggleSelectors.forEach((selector) => {
    document.querySelectorAll(selector).forEach((button) => {
      button.addEventListener('click', () => {
        // Delay so AdminLTE toggles classes first.
        window.setTimeout(persistSidebarState, 310);
      });
    });
  });

  if (window.jQuery) {
    window.jQuery(document).on('collapsed.lte.pushmenu shown.lte.pushmenu', () => {
      window.requestAnimationFrame(persistSidebarState);
    });
  }
};

let desiredFullscreenState = null;
let fullscreenRestoreHandler = null;

const enterFullscreen = () => {
  const element = document.documentElement;

  if (element.requestFullscreen) {
    return element.requestFullscreen();
  }
  if (element.webkitRequestFullscreen) {
    return element.webkitRequestFullscreen();
  }
  if (element.msRequestFullscreen) {
    return element.msRequestFullscreen();
  }

  return Promise.reject(new Error('Fullscreen API not supported'));
};

const removeFullscreenRestoreHandlers = () => {
  if (!fullscreenRestoreHandler) {
    return;
  }

  FULLSCREEN_GESTURE_EVENTS.forEach((eventName) => {
    document.removeEventListener(eventName, fullscreenRestoreHandler);
  });

  fullscreenRestoreHandler = null;
};

const scheduleFullscreenRestore = () => {
  if (desiredFullscreenState !== 'on' || isFullscreenActive()) {
    removeFullscreenRestoreHandlers();
    return;
  }

  if (fullscreenRestoreHandler) {
    return;
  }

  fullscreenRestoreHandler = () => {
    removeFullscreenRestoreHandlers();

    const result = enterFullscreen();
    if (result && typeof result.then === 'function') {
      result.catch(() => {
        // If the request fails (e.g. still no gesture), wait for the next gesture.
        scheduleFullscreenRestore();
      });
    }
  };

  FULLSCREEN_GESTURE_EVENTS.forEach((eventName) => {
    document.addEventListener(eventName, fullscreenRestoreHandler, { once: true });
  });
};

const persistFullscreenState = () => {
  const isActive = isFullscreenActive();
  const state = isActive ? 'on' : 'off';

  if (state === 'on') {
    setStoredValue(STORAGE_KEYS.fullscreen, 'on');
  } else {
    removeStoredValue(STORAGE_KEYS.fullscreen);
  }

  desiredFullscreenState = state;
  scheduleFullscreenRestore();
};

const setupFullscreenPersistence = () => {
  const stored = getStoredValue(STORAGE_KEYS.fullscreen);
  desiredFullscreenState = stored === 'on' ? 'on' : 'off';

  FULLSCREEN_EVENT_NAMES.forEach((eventName) => {
    document.addEventListener(eventName, persistFullscreenState);
  });

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-widget="fullscreen"]');
    if (trigger) {
      window.requestAnimationFrame(() => {
        persistFullscreenState();
      });
    }
  });

  scheduleFullscreenRestore();
};

const initLayoutPersistence = () => {
  if (!document.body) {
    return;
  }

  setupSidebarPersistence();
  setupFullscreenPersistence();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLayoutPersistence);
} else {
  initLayoutPersistence();
}
