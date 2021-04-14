projectDetailsTabbar.addTab('training_module', 'Trainings');
var training_module = projectDetailsTabbar.cells('training_module');
var trainingLayout = training_module.attachLayout('1C');
trainingLayout.cells('a').hideHeader();
trainingLayout.cells('a').attachURL("https://83.98.243.184/home.php?projectId=1");

