import shutil, os, time


# exs = os.path.exists("/home/ian/Documents/Projects/CEM/bin/compiler/howler.core.js")
# print(exs)

# exit()




desktopFolder = "/home/ian/Desktop/"

projectFolder = "/home/ian/Documents/Projects/CEM"
outputFolder = "/home/ian/Desktop/CEM"
closureFolder = "{}/bin/compiler".format(projectFolder)
jsFolder = "{}/js".format(outputFolder)
projectCssFolder = "{}/css".format(projectFolder)
outputCssFolder = "{}/css".format(outputFolder)


# Replace CB str in file
def replaceCb(file):
    fin = open(file, "rt")
    data = fin.read()
    data = data.replace("cb={automatically-updated}", "cb={}".format(time.time()))
    fin.close()

    fin = open(file, "wt")
    fin.write(data)
    fin.close()

def rmfile(path):
    if(os.path.exists(path)):
        os.remove(path)

def rmtree(path):
    if(os.path.exists(path)):
        shutil.rmtree(path)



os.chdir(closureFolder)

# delete publish
rmtree(outputFolder)
# copy from source
shutil.copytree(projectFolder, outputFolder)



rmtree("{}/.vscode".format(outputFolder))
rmtree("{}/.git".format(outputFolder))
# rmtree("{}/bin".format(outputFolder))

rmfile("{}/README.md".format(outputFolder))

for filename in os.listdir(jsFolder):
    if ".min" in filename or ".htaccess" in filename:
        continue

    full_path = os.path.join(jsFolder, filename)
    full_path2 = os.path.join(closureFolder, filename)
    
    cmd = 'java -jar closure-compiler.jar --js "{}" --js_output_file {}'.format(full_path, filename)
    os.system(cmd)

    print("Minifying file: {}".format(filename))
    # print('Exists: {} - File: "{}"'.format(os.path.exists(full_path2), full_path2))

    if os.path.exists(full_path2):
        shutil.move(full_path2, full_path)


# minify css
arCssFiles = ["index","theme"]
[os.remove(i) for i in ["{}/{}.css".format(outputCssFolder, i) for i in arCssFiles]]
strCssFiles = " ".join(['"{}/{}.css"'.format(projectCssFolder, i) for i in arCssFiles])
cmd = 'java -jar closure-stylesheets.jar --output-file "{}" {}'.format("{}/css/index.min.css".format(outputFolder), strCssFiles)
os.system(cmd)


# replace cb id
replaceCb("{}/index.php".format(outputFolder))
replaceCb("{}/login.php".format(outputFolder))
replaceCb("{}/js/index.js".format(outputFolder))


# rsync
os.chdir(outputFolder)
cmd = 'rsync -avh -e ssh ~/Desktop/"CEM/" sdr@192.168.0.104:/var/www/html/.'
print(cmd)
os.system(cmd)


