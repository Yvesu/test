import React, { Component } from 'react'
import { Card,DatePicker,Button,Layout  } from 'antd'
import moment from 'moment'
const {RangePicker } = DatePicker
const dateFormat = 'YYYY/MM/DD'
import ActiveAmount from './cardTables/ActiveAmount'
import IpAmount from './cardTables/IpAmount'
import { getTimeDistance } from 'utils/ChangeRangePicker'

const tabList = [{
  key: 'activeAmount',
  tab: '活跃量',
}, {
  key: 'ipAmount',
  tab: 'IP',
}];

class MonitorCharts extends Component{
  constructor(props){
    super(props)
    this.state={
      cardKey: 'activeAmount',
      rangePickerValue:[],
      todayColor:false,
      weekColor:false,
      monthColor:false,
      yearColor:false,
    }
  }
  onTabChange = (key) => {

    this.setState({ ['cardKey']:key });
  }
  handleRangePickerChange = (rangePickerValue) => {
    this.setState({
      rangePickerValue,
    });
  }
  handleSelectDate = (type) => {
    this.setState({
      rangePickerValue: getTimeDistance(type),
    });
    if(type==='today'){
      this.handleChangeInitColor()
      this.setState({
        todayColor:true,
      })
    }
    if(type==='week'){
        this.handleChangeInitColor()
        this.setState({
          weekColor:true,
        })
    }
    if(type==='month'){
      this.handleChangeInitColor()
      this.setState({
        monthColor:true,
      })
    }
    if(type ==='year'){
      this.handleChangeInitColor()
      this.setState({
        yearColor:true
      })
    }
  }
  handleChangeInitColor=()=>{
    this.setState({
      todayColor:false,
      weekColor:false,
      monthColor:false,
      yearColor:false
    })
  }

  render(){
    const { rangePickerValue } =this.state
    const contentList = {
      activeAmount: <ActiveAmount />,
      ipAmount: <IpAmount />,
    };
    return(
      <Layout className='monitor_echarts_box'>
        <Card
          style={{ width: '100%' }}
          tabList={tabList}
          extra={<div className='card_echarts_right_box'>
            <p className="change_date_box">
              <span className={this.state.todayColor===true? 'changeColorClick' : ''}
                  onClick={()=>this.handleSelectDate('today')}>
                今日
              </span>
              <span className={this.state.weekColor===true? 'changeColorClick' : ''}
                  onClick={()=>this.handleSelectDate('week')}>
                本周
              </span>
              <span className={this.state.monthColor===true? 'changeColorClick' : ''}
                  onClick={()=>this.handleSelectDate('month')}>
                本月
              </span>
              <span className={this.state.yearColor===true? 'changeColorClick' : ''}
                  onClick={()=>this.handleSelectDate('year')}>
                全年
              </span>
            </p>
            <RangePicker
              value={rangePickerValue}
              onChange={this.handleRangePickerChange}
           />
          </div>}
          onTabChange={this.onTabChange}
        >
          {contentList[this.state.cardKey]}
        </Card>

      </Layout>
    )
  }
  componentDidMount(){
    this.handleSelectDate('today')

  }
}

export default MonitorCharts
