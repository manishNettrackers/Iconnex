# Creates a data model colum a set definition from a table
if [ $# -ne 2 ]; then
    echo "usage: sch2php.sh table_name classname"
    exit
fi
dbschema -d centurion -t $1 > sch.out

echo "<?php"
echo "/**"
echo "* $2"
echo "*"
echo "* Datamodel for table $1"
echo "*"
echo "*/"
echo "
class $2 extends DataModel
{
    function __construct(\$connector = false, \$initialiserArray = false)
    {"

echo "        \$this->columns = array ("
cat sch.out | sed -n "/  (/,/ )/p" | sed "s/^ *//" | grep -v "^($" | grep -v "^);" | sed "s/,$//" | while read i
do

    col=`echo $i | cut -d " " -f1`
    tp=`echo $i | cut -d" " -f2`
    rest=`echo $i | cut -d" " -f3-`
    if [ "$rest" != "" ]; then
        rest=", ${rest}"
    fi

    tp="\"$tp\""
    tp=`echo $tp | sed "s/char(\(.*\))\"/char\", \1/"`

    nm=$col;
    for a in A B C D E F G H I J K L M N O P Q R S T U V W X Y Z
    do
        c2=`echo $a | tr 'A-Z' 'a-z'`
        nm=`echo $nm | sed "s/_$c2/$a/g"`
    done
    if [ "$col" = "" ]; then
        continue;
    fi

    if [ "$tp" = "\"serial\"" ]; then
        rest=""
    fi


    x="\"$col\" => new DataModelColumn(\$this->connector,  \"$col\", $tp $rest),"
    x=`echo $x | sed -e "s/,  )/ )/" -e "s/, )/ )/"`
    echo "            $x";
done

echo "            );"
echo ""
echo "        \$this->tableName = \"$1\";";
echo "        \$this->dbspace = \"centdbs\";";
echo "        \$this->keyColumns = array(...);";
echo "        parent::__construct(\$connector, \$initialiserArray);";
echo ""
echo "    }"
echo "}"
echo "?>"


