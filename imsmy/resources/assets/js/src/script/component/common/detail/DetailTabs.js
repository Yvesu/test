import React, { Component } from 'react'

import { Tabs, Radio, Pagination } from 'antd'
const TabPane = Tabs.TabPane
import fetchPost from 'utils/fetch'

class DetailTabs extends Component{
  constructor(props) {
    super(props);
    this.state = {
     replyCount:"",   //评论总数
     trophyCount:"",    //荣誉总数
     //replys:"",     //推广总数 这个还没有
     checkCount:"",   //操作记录
     HotReply:[{}],   //热评
     NewReply:[{}],   //最新评论
     HotFatherReply:[],   //热评里边的父级评论
     NewFatherReply:[]    //最新评论的父级评论
   }

  }

  GetDetailTabsData(){
    fetchPost.post({
      uri:`/api/admins/video/details/${this.props.id}`,
      callback:(res)=>{
        this.setState({
          replyCount:res.replys_count,
          trophyCount:res.trophy_count,
          // replys:res.replys_count,  //还是推广的留个地方先
          checkCount:res.check_count,
          HotReply:res.hot_replys,
          NewReply:res.replys,
          HotFatherReply:res.hot_replys.father_reply,
          NewFatherReply:res.replys.father_reply
        })
      }
    })
  }

  render(){
    return(
      <div className="detail_tabs">
          <Tabs
          defaultActiveKey="1"
          style={{ width:"100%", height: "100%" }}
          >
            <TabPane tab={`9.0分 评论: ${this.state.replyCount}`} key="1">
              <div className="hot_reply_box">
                <h4>热评</h4>
                <div>
                  {this.state.HotReply.map((value,index)=>{
                    return <p>
                      <b>{value.user_nickname}{" : "}</b>
                      {value.content}
                    </p>
                  })}
                </div>
              </div>
              <div className="new_reply_box" >
                <h4>最新</h4>
                <div>
                  {this.state.NewReply.map((value,index)=>{
                    return <p>
                      <b>{value.user_nickname}{" : "}</b>
                      {value.content}
                    </p>
                  })}
                </div>
              </div>

            </TabPane>
            <TabPane tab={`荣誉: ${this.state.trophyCount}`} key="2">荣誉</TabPane>
            <TabPane tab={`推广: ${0}`} key="3">推广</TabPane>
            <TabPane tab={`操作记录: ${this.state.checkCount}`} key="4">操作记录</TabPane>
          </Tabs>
      </div>
    )
  }
  componentDidMount(){
    // console.log(this.props.id);
    this.GetDetailTabsData()
  }
  componentWillReceiveProps(nextPorps){
    // console.log(this.props);
    this.GetDetailTabsData()
  }
}

export default DetailTabs
