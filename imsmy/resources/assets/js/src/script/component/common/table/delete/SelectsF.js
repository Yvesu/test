import React, { Component } from 'react'
import {hashHistory} from 'react-router'
import FetchPost from 'utils/fetch'
//选项组件
import { Input, Button, Icon, Select, DatePicker, Spin } from 'antd'
const Search = Input.Search
const { MonthPicker, RangePicker } = DatePicker

class VideoSelect extends Component {
  constructor(props){
    super(props)
    this.hisLoca = hashHistory.getCurrentLocation();
    this.state={
      loading:false,
      count:"",
      today_count:'',
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
              <span>总共：<b>{this.state.count} </b>条视频</span>
              <span>今日新增：<b>{this.state.today_count}</b>条</span>
            </h3>
            <p>
              <span>搜索：
                <Search placeholder="请输入关键字" defaultValue={this.state.searchVal} style={{ width: "16.7%" }} onSearch={this.onSearch} />
              </span>
              <Select style={{width: "13%" , marginLeft:"2%"}} defaultValue="类型 : 参赛作品" onSelect={this.onSelect}>
                <Option value="官方发布">类型: 官方发布</Option>
                <Option value="认证用户">类型: 认证用户</Option>
              </Select>

              <RangePicker style={{width: "18%" , marginLeft:"2%"}} placeholder={['开始', '结束']} onChange={this.onChange} />

              <Select style={{width: "13%" , marginLeft:"2%"}} defaultValue="时长 : 不限">
                <Option value="六十分钟">时长: 六十分钟</Option>
                <Option value="两个小时">时长: 两个小时</Option>
              </Select>
              <Select style={{width: "13%" , marginLeft:"2%"}} defaultValue="播放量 : 1万以上">
                <Option value="5万以上">播放量 : 5万以上</Option>
                <Option value="10万以上">播放量 : 10万以上</Option>
              </Select>
              <Button type="primary" onClick={this.BtnFilter} style={{width: "10%" , marginLeft:"3%"}}>确定</Button>
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
          count:res.count,
          today_count:res.today_count
        })
      }
    })
  }
}

export default VideoSelect
