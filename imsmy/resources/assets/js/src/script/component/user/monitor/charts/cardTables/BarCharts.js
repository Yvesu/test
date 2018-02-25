import React,{ Component } from 'react'
import { Bar } from 'ant-design-pro/lib/Charts'
import Fetch from 'utils/fetch'
var test=[
  {'name':'00:00','number':'0'},{'name':'02:00','number':'8'},
  {'name':'04:00','number':'4'},{'name':'06:00','number':'3'},
  {'name':'08:00','number':'7'},{'name':'10:00','number':'15'},
  {'name':'12:00','number':'1'},{'name':'14:00','number':'5'},
  {'name':'16:00','number':'6'},{'name':'18:00','number':'6'},
  {'name':'20:00','number':'2'},{'name':'22:00','number':'2'}
]

const salesData = [];
for (let i = 0; i < 12; i+=1) {
  salesData.push({
    x: test[i].name,
    y: Math.floor(test[i].number*100),
  });

}
class BarCharts extends Component{
  render(){
    return(
      <div>
        <Bar
          height={380}
          title={this.props.title? this.props.title : ''}
          data={salesData}
        />
      </div>
    )
  }
  componentDidMount(){
    console.log(this.props);
    Fetch.post({
      uri:'/api/admins/user/supervisory/index',
      callback:(res)=>{
        console.log(res,'biao');
      }
    })
  }
}

export default BarCharts
