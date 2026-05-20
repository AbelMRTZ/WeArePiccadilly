  // Codigo para Claude
  app.use(express.json());

  const syncPodcast = require('./sync-endpoint');
  app.use('/api', syncPodcast);
