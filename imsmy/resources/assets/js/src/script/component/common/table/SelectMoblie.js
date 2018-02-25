import React, { Component } from 'react'
import {hashHistory} from 'react-router'
import FetchPost from 'utils/fetch'
//选项组件
import { Input, Button, Icon, Select, DatePicker, Spin } from 'antd'
const Search = Input.Search
const { MonthPicker, RangePicker } = DatePicker

class SelectMoblie extends Component {
  constructor(props){
    super(props)
    this.hisLoca = hashHistory.getCurrentLocation();
    this.state={
      loading:false,
      sum:"",
      todaynew:'',
      type:[],
      operator:[],
      time:[],
      integral:[],
      count:[],
      searchVal:this.hisLoca.query.searchVal,
      selectval:this.hisLoca.query.selectval,
      dataP:this.hisLoca.query.dataP
    }
    // console.log(hashHistory.getCurrentLocation())
    this.onSearch = this.onSearch.bind(this);
    this.onSelect = this.onSelect.bind(this);
    this.onChange = this.onChange.bind(this);
    this.BtnFilter = this.BtnFilter.bind(this);

  }
  BtnFilter(){
      var params = {
          searchVal:this.state.searchVal,
          selectval:this.state.selectval,
          dataP:this.state.dataP
      }

      //跳转带参数
      hashHistory.push({
          pathname:this.hisLoca.pathname,
          query:params
      })

  };
  componentDidMount(){
    var that = this;
   //调到上一个页面
    //browserHistory.push(path);
  }
  onSearch(value){
    this.setState({
        searchVal:value
    })
  }
  onSelect(value){
    console.log(value);
    this.setState({
        selectval:value
    })
  }
  onChange(value){
     this.setState({
        dataP:value
    })
  }
  render(){
    return(
      <Spin spinning={this.state.loading}>
        <div className="select_filter">
            <h3>
              <span>总共：<b>{this.state.sum} </b>条视频</span>
              <span>今日新增：<b>{this.state.todaynew}</b>条</span>
            </h3>
            <p>
              <span>搜索：
                <Search placeholder="请输入关键字" defaultValue={this.state.searchVal} style={{ width: "13%" }} onSearch={this.onSearch} />
              </span>
              <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}} defaultValue="分类 : 电影" onSelect={this.onSelect}>
                {
                  this.state.type.map((value,index)=>{
                    return(
                      <Option value={value.id}>{value.name}</Option>
                    )
                  })
                }
              </Select>
              <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}} defaultValue="操作员 : 所有人">
                {
                  this.state.operator.map((value,index)=>{
                    return(
                      <Option value={value.id}>{value.name}</Option>
                    )
                  })
                }
              </Select>
              <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}} defaultValue="时间 : 一天内">
                {
                  this.state.time.map((value,index)=>{
                    return(
                      <Option value={value.label}>{value.des}</Option>
                    )
                  })
                }
              </Select>
              <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}} defaultValue="费用 : 免费" onSelect={this.onSelect}>
                {
                  this.state.integral.map((value,index)=>{
                    return(
                      <Option value={value.integral}>{value.integral}</Option>
                    )
                  })
                }
              </Select>
              <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}} defaultValue="下载量 : 1万以上">
                {
                  this.state.count.map((value,index)=>{
                    return(
                      <Option value={value.count}>{value.count}</Option>
                    )
                  })
                }
              </Select>
              <Button type="primary" onClick={this.BtnFilter}
                style={{width: "8%", marginLeft:"3%"}}>确定</Button>
            </p>
        </div>
      </Spin>
    )
  }
  componentDidMount(){
    this.setState({ loading: true })
    FetchPost.post({
      uri:this.props.uri,
      callback:(res)=>{
        this.setState({
          loading:false,
          sum:res.sum,
          todaynew:res.todaynew,
          type:res.type,
          operator:res.operator,
          time:res.time,
          integral:res.integral,
          count:res.count
        })
      }
    })
  }
}

export default SelectMoblie
