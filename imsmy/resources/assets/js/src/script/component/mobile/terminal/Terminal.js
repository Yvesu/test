import React, { Component } from 'react'
import { Layout, Radio, DatePicker   } from 'antd'
import moment from 'moment'
import 'moment/locale/zh-cn'
const RadioButton = Radio.Button
const RadioGroup = Radio.Group
const { RangePicker } = DatePicker
const dateFormat = 'YYYY/MM/DD'

import PhoneModel from './terminalChildren/PhoneModel'
import PhoneRes from './terminalChildren/PhoneRes'
import PhoneOS from './terminalChildren/PhoneOS'

//设备终端
class Terminal extends Component {
  constructor(props){
    super(props)
    this.state={
      valueChange:"1"
    }
    // console.log(this);
  }
  onChange(e) {
    const targetValue = e.target.value
    // console.log(targetValue);
    this.setState({
      valueChange:targetValue
    })
  }
  render(){
    return(
      <Layout>
        <div className="terminal_title_box">
            <h2>应用启动：12393210 次</h2>
            <p>
              <span>iOS：<em>38%</em> </span>
              <span>Android：<em>62%</em></span>
            </p>
        </div>
        <div className="terminal_contents_box">
          <RadioGroup onChange={this.onChange.bind(this)} defaultValue="1">
            <RadioButton value="1">机型</RadioButton>
            <RadioButton value="2">分辨率</RadioButton>
            <RadioButton value="3">操作系统</RadioButton>
          </RadioGroup>
          <RangePicker
            defaultValue={[moment('2017/11/01', dateFormat), moment('2017/12/01', dateFormat)]}
            format={dateFormat}
            style={{float:'right'}}
          />
          <div style={{marginTop:20}}>
            {
              (this.state.valueChange === "1")?  (<PhoneModel/>) :
              (this.state.valueChange === "2")? (<PhoneRes/>) : (<PhoneOS/>)
            }

          </div>
        </div>
      </Layout>
    )
  }
}

export default Terminal
