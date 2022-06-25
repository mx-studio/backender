@ECHO OFF
for /F "tokens=1,2,3 delims=." %%G IN ('composer config version') do (
    SET VER_MAJ=%%G
    SET VER_MIN=%%H
    SET VER_PATCH=%%I
)
SET /A VER_PATCH=VER_PATCH+1
SET VER=%VER_MAJ%.%VER_MIN%.%VER_PATCH%
call composer config version %VER%
git commit -a -m %VER%
git tag %VER%
git push
git push --tags
echo Version updated to %VER%
