@ECHO OFF
for /F "tokens=1,2,3 delims=." %%G IN ('composer config version') do (
    SET VER_MAJ=%%G
    SET VER_MIN=%%H
    SET VER_PATCH=%%I
)
SET /A VER_PATCH=VER_PATCH+1
SET VER=%VER_MAJ%.%VER_MIN%.%VER_PATCH%
echo done1
call composer config version %VER%
echo done2
git commit -a -m %VER%
echo done3
git tag %VER%
git push --all --tags
echo Version updated to %VER%
