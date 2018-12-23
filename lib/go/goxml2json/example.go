package main
import (
	"fmt"
	"strings"
"io/ioutil"
	xj "github.com/basgys/goxml2json"
)


func main() {
 b, err := ioutil.ReadFile("/opt/lampp/htdocs/ai_plantform/tmp/7890.xml")
    if err != nil {
        fmt.Print(err)
    }
result := strings.Replace(string(b),"\n","",1)
	
	xml := strings.NewReader(result)
	json, err := xj.Convert(xml)
	if err != nil {
		panic("That's embarrassing...")
	}
	fmt.Println(json.String())
	// {"hello": "world"}
}
