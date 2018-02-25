import React, { Component } from 'react'
import {hashHistory} from 'react-router'
import Fetch from 'utils/fetch'
//选项组件
import { Input, Button, Icon, Select, Spin } from 'antd'
const Search = Input.Search

class Selects extends Component {
  constructor(props){
    super(props)
    this.state={
      loading:false,
      sumnum:"",
      todaynew:'',
      type:[], //类型
      operator:[],//操作人员
      time:[],//时间
      duration:[],//时长
      integral:[],//下载费用
      count:[],//下载量
      searchValue:"", //搜索关键字
      typeValue:'', //类型
      operatorValue:"",//操作人员
      timeValue:'', //选择的事件
      durationValue:'', //时长
      integralValue:"",//下载费用
      countValue:'',//下载量

    }

  }
  hanldSelectType=()=>{
    Fetch.post({
      uri:this.props.type,
      callback:(res)=>{
        this.setState({
          type:res.data
        })
      }
    })
  }
  handleSelectOperator=()=>{
    Fetch.post({
      uri:this.props.operator,
      callback:(res)=>{
        this.setState({
          operator:res.data
        })
      }
    })
  }
  handleSelectTime=()=>{
    Fetch.post({
      uri:this.props.time,
      callback:(res)=>{
        this.setState({
          time:res.data
        })
      }
    })
  }
  handleSelectDuration=()=>{
    Fetch.post({
      uri:this.props.duration,
      callback:(res)=>{
        this.setState({
          duration:res.data
        })
      }
    })
  }
  handleSelectIntegral=()=>{
    Fetch.post({
      uri:this.props.integral,
      callback:(res)=>{
        this.setState({
          integral:res.data
        })
      }
    })
  }
  handleSelectCount=()=>{
    Fetch.post({
      uri:this.props.count,
      callback:(res)=>{
        this.setState({
          count:res.data
        })
      }
    })
  }
  handleSearchValue=(e)=>{
    this.setState({
      searchValue:e.target.value
    })
  }
  handleTypeValue=(value)=>{
    this.setState({
      typeValue:value
    })
    // this.props.typeValue(value)
  }
  handleOperatorValue=(value)=>{
    this.setState({
      operatorValue:value
    })
    // this.props.operatorValue(value)

  }
  handleTimeValue=(value)=>{
    this.setState({
      timeValue:value
    })
    // this.props.timeValue(value)

  }
  handleDurationValue=(value)=>{
    this.setState({
      durationValue:value
    })
  // this.props.durationValue(value)
  }
  handleIntegralValue=(value)=>{
    this.setState({
      integralValue:value
    })
  }
  handleCountValue=(value)=>{
    this.setState({
      countValue:value
    })
    // this.props.countValue(value)
  }
  handleChangeTable=()=>{
    if(this.props.searchValue){
      this.props.searchValue(this.state.searchValue)
    }
    if(this.props.typeValue){
      this.props.typeValue(this.state.typeValue)
    }
    if(this.props.operatorValue){
      this.props.operatorValue(this.state.operatorValue)
    }
    if(this.props.timeValue){
      this.props.timeValue(this.state.timeValue)
    }
    if(this.props.durationValue){
      this.props.durationValue(this.state.durationValue)
    }
    if(this.props.countValue){
      this.props.countValue(this.state.countValue)
    }

  }

  handleChangeSearchValue=(e)=>{
    this.setState({
      searchValue:e.target.value
    })
    this.props.searchValue(e.target.value)
  }

  render(){
    return(
      <Spin spinning={this.state.loading}>
        <div className="select_filter">
            <h3>
              <span>总共：<b>{this.state.sumnum} </b>条视频</span>
              <span>今日新增：<b>{this.state.todaynew}</b>条</span>
            </h3>
            <p>

              <span>搜索：
                <Search placeholder="请输入关键字"  style={{ width: "13%" }}
                    onSearch={this.handleSearchValue}
                    onChange={this.handleSearchValue}
                    onPressEnter={this.handleChangeSearchValue}
                />
              </span>
              {
                this.props.type && this.props.type!=='' ?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                   placeholder='分类:电影'
                    onFocus={this.hanldSelectType}
                    onChange={this.handleTypeValue}
                    allowClear={true}
                  >
                    {
                      this.state.type.map((value,index)=>{
                        return(
                          <Option value={value.id}>{value.type}</Option>
                        )
                      })
                    }
                  </Select> : null
              }

              {
                this.props.operator && this.props.operator !==''?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                  placeholder='操作员:所有人'
                    onFocus={this.handleSelectOperator}
                    onChange={this.handleOperatorValue}
                    allowClear={true}

                  >
                  {
                    this.state.operator.map((value,index)=>{
                      return(
                        <Option value={value.operator_id}>{value.operator_name}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {
                this.props.time && this.props.time !==''?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='时间: 不限时间'
                    onFocus={this.handleSelectTime}
                    onChange={this.handleTimeValue}
                    allowClear={true}

                  >
                  {
                    this.state.time.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }

              {
                this.props.duration && this.props.duration !==''?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='时长:不限'
                    onFocus={this.handleSelectDuration}
                    onChange={this.handleDurationValue}
                    allowClear={true}

                  >
                  {
                    this.state.duration.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {
                this.props.integral && this.props.integral !==''?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='费用: 免费'
                    onFocus={this.handleSelectIntegral}
                    onChange={this.handleIntegralValue}
                    allowClear={true}

                  >
                  {
                    this.state.integral.map((value,index)=>{
                      return(
                        <Option value={value.intergal}>{value.intergal}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {
                this.props.count && this.props.count !==''?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='下载量: 1万以上'
                    onFocus={this.handleSelectCount}
                    onChange={this.handleCountValue}
                    allowClear={true}
                  >
                  {
                    this.state.count.map((value,index)=>{
                      return(
                        <Option value={value.count}>{value.count}</Option>
                      )
                    })
                  }
                </Select> : null
              }


              <Button type="primary" onClick={this.handleChangeTable}
                style={{width: "8%", marginLeft:"3%"}}>确定</Button>
            </p>
        </div>
      </Spin>
    )
  }
  componentDidMount(){
    this.setState({ loading: true })
    Fetch.post({
      uri:this.props.uri,
      callback:(res)=>{
        this.setState({
          loading:false,
          sumnum:res.sumnum,
          todaynew:res.todaynew,
        })
      }
    })
  }
}

export default Selects
