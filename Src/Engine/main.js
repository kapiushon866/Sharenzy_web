const { app, BrowserWindow, Menu } = require('electron');
const path = require('path'); 

let mainWindow;

function createWindow() {
  try {
    mainWindow = new BrowserWindow({
      width: 1200,
      height: 800,
      icon: path.join(__dirname, 'Engine', 'Images', 'Ico', 'sharenzy_main.ico'), 
      webPreferences: {
        nodeIntegration: true
      }
    });

    mainWindow.loadFile(path.join(__dirname, '../En/index.html'));
    mainWindow.setTitle('Sharenzy');

    const menu = Menu.buildFromTemplate([]);
    Menu.setApplicationMenu(menu);

    mainWindow.on('closed', () => {
      mainWindow = null;
    });

  } catch (error) {
    console.error("Failed to create main window:", error);
  }
}

app.whenReady().then(() => {
  createWindow();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
