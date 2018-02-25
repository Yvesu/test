import React, { Component } from 'react'
import { Radio } from 'antd'
const RadioButton = Radio.Button
const RadioGroup = Radio.Group
import RaceList from '../common/RaceList'

class RacePartake extends Component{
  constructor(props){
    super(props)
    this.state={

    }
  }
  handleChangeRadioList=(e)=>{
    const targetValue = e.target.value
    console.log(targetValue);
    this.setState({
      valueChange:targetValue
    })
  }
  render(){
    return(
      <div className="race_partake">
        <div className="race_head_info">
          <h3>共参与 <b>2</b> 场竞赛，<b>1</b> 场进行中 ，<b>1</b> 场已结束。</h3>
          <RadioGroup onChange={this.handleChangeRadioList} defaultValue="1">
            <RadioButton value="1">全部</RadioButton>
            <RadioButton value="2">进行中</RadioButton>
            <RadioButton value="3">已结束</RadioButton>
          </RadioGroup>
        </div>
        <RaceList raceBtnText='查看详情' uri='/api/test/filmfest' active='4'/>
      </div>
    )
  }
}

export default RacePartake
