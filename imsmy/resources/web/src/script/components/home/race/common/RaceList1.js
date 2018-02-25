import React, { Component } from 'react'
import { Button } from 'antd'
import Fetch from 'utils/fetch'
class RaceList extends Component{
  constructor(props){
    super(props)
    this.state={
      data:[],
      loadingMore:false,
      page:1
    }
  }
  handleGetRaceListData=()=>{
    console.log(this.state.loadingMore);
    let formData = new FormData()
    if(this.props.active){
      formData.append('active',this.props.active)
    }
    formData.active
    if(this.state.loadingMore===true){
      const page = ++this.state.page
      console.log(page,'zouliuceng');
      // formData.append('page',page)
    }else{
      console.log('加载更多为false');
    }
    Fetch.post({
      uri:this.props.uri,
      callback:(res)=>{
        console.log(res,'jingsai');
        this.setState({
          data:res.data
        })
      },
      formData:formData
    })
  }
  test=()=>{
    // const page = ++this.state.page
    // console.log(page,"haha");
    // this.setState({
    //   loadingMore:true
    // })
    this.state.loadingMore=true
    this.handleGetRaceListData()
  }
  testOne=()=>{

    console.log();
  }
  render(){
    return(
      <div className="race_list_box">
        {
          this.props.uri?  this.state.data.map((value,index)=>{
            return(
              <div className="ract_list_only" key={index}>
                <div className='list_only_title'>
                  <h2>
                    <span>{value.name}</span>
                    <span style={{display:value.end? 'block' : 'none'}}>已结束</span>
                  </h2>
                  <Button className={value.end? 'race_is_end' : ''} onClick={this.testOne}>
                    {this.props.raceBtnText? this.props.raceBtnText : '提交作品'}
                  </Button>
                </div>
                <div className='list_only_info'>
                  <p>
                    <span>提交截止</span>
                    <span>节日期</span>
                    <span>费用</span>
                  </p>
                  <p>
                    <span>{value.submit_end_time}</span>
                    <span>{value.festTime}</span>
                    <span>{value.cost===0? '无' : value.cost }</span>
                  </p>
                  <p>
                    <span>接受类别</span>
                    <span>举办地</span>
                  </p>
                  <p>
                    <span>{value.type}</span>
                    <span>{value.address}</span>
                  </p>
                </div>
              </div>
            )
          }) : <div className="ract_list_only">
            <div className='list_only_title'>
              <h2>第二十五届北京大学生电影节
                <span style={{display:'none'}}>已结束</span>
              </h2>
              <Button className=''>查看详情</Button>
            </div>
            <div className='list_only_info'>
              <p>
                <span>提交截止</span>
                <span>节日期</span>
                <span>费用</span>
              </p>
              <p>
                <span>2018年3月18日</span>
                <span>2018年5月14日 - 2018年5月18日</span>
                <span>无</span>
              </p>
              <p>
                <span>接受类别</span>
                <span>举办地</span>
              </p>
              <p>
                <span>实验，新媒体/网络，短片</span>
                <span>中国 . 北京</span>
              </p>
            </div>
          </div>
        }
        <Button onClick={this.test}>加载更多</Button>
      </div>
    )
  }
  componentDidMount(){
    if(this.props.uri){
      this.handleGetRaceListData()
    }
  }
}

export default RaceList
